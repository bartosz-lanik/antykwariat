<?php

namespace App\Controller;

use App\Entity\CategoryTranslation;
use App\Entity\MyCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ObjectRepository $collectionRepository;
    private ObjectRepository $categoryTranslationRepository;
    private RequestStack $requestStack;
    private PaginatorInterface $paginator;

    public function __construct
    (
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator
    )
    {
        $this->entityManager = $entityManager;
        $this->paginator = $paginator;
        $this->requestStack = $requestStack;
        $this->collectionRepository = $this->entityManager->getRepository(MyCollection::class);
        $this->categoryTranslationRepository = $this->entityManager->getRepository(CategoryTranslation::class);
    }

    /**
     * @Route("/")
     */
    public function indexNoLocale(): Response
    {
        return $this->redirectToRoute('index', ['_locale' => 'pl']);
    }

    /**
     * @Route(
     *     "/{_locale}",
     *     name="index",
     *     methods={"GET"},
     *     requirements={
     *         "_locale": "%app_locales%",
     *     }
     * )
     */
    public function index(): Response
    {
        $myCollections = $this->collectionRepository->findAll();
        $categories = $this->categoryTranslationRepository->findBy(['locale' => $this->requestStack->getCurrentRequest()->getLocale()]);

        $paginatedMyCollections = $this->paginator->paginate(
            $myCollections,
            $this->requestStack->getCurrentRequest()->query->getInt('page', 1),
            $this->requestStack->getCurrentRequest()->query->getInt('limit', 9)
        );

        return $this->render('index/index.html.twig', [
            'myCollections' => $paginatedMyCollections,
            'categories' => $categories
        ]);
    }

    /**
     * @Route(
     *     "/{_locale}/export/",
     *     name="index_export",
     *     methods={"GET"},
     *     requirements={
     *         "_locale": "%app_locales%",
     *     }
     * )
     */
    public function export(): BinaryFileResponse
    {
        $myCollections = $this->collectionRepository->findAll();
        if(!$myCollections) {
            throw $this->createNotFoundException('Collections does not exist');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Zdjęcie');
        $sheet->setCellValue('B1', 'Tytuł');
        $sheet->setCellValue('C1', 'Opis');
        $sheet->setCellValue('D1', 'Atrybuty');

        $sheet->getStyle('A1:D99')->getAlignment()->setWrapText(true)->setHorizontal('center')->setVertical('center');

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setAutoSize(true);

        foreach(range(2,99) as $rd) {
            $sheet->getRowDimension($rd)->setRowHeight(150);
        }

        $a = 2;
        foreach ($myCollections as $collection) {
            $drawing = new Drawing();
            $drawing->setName('Image');
            $drawing->setDescription('Image');
            $drawing->setPath('assets/img/'.$collection->getImage());
            $drawing->setCoordinates('A'.$a);
            $drawing->setWidthAndHeight(100,150);
            $drawing->setResizeProportional(true);
            $drawing->setWorksheet($spreadsheet->getActiveSheet());

            $sheet->setCellValue('B'.$a, $collection->getName());
            $sheet->setCellValue('C'.$a, $collection->getDescription());

            $attr = '';
            $attributes = $collection->getMyCollectionAttributes()->toArray();
            foreach ($attributes as $attribute) {
                $attr .= $attribute->getAttribute()->getName().': '.$attribute->getValue().PHP_EOL;
            }
            $sheet->setCellValue('D'.$a, $attr);
            $a++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('exports/all_collections.xlsx');
        $response = new BinaryFileResponse('exports/all_collections.xlsx');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'all_collections.xlsx');
        return $response;
    }
}
