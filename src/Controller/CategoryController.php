<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Repository\CategoryRepository;
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
use Symfony\Component\HttpFoundation\UrlHelper;

class CategoryController extends AbstractController
{
    private RequestStack $requestStack;
    private string $getRequestLocale;
    private EntityManagerInterface $entityManager;
    private ObjectRepository $categoryTranslationRepository;
    /**
     * @var CategoryRepository|ObjectRepository
     */
    private $categoryRepository;
    private PaginatorInterface $paginator;
    private UrlHelper $urlHelper;

    public function __construct
    (
        PaginatorInterface $paginator,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        UrlHelper $urlHelper
    )
    {
        $this->paginator = $paginator;
        $this->requestStack = $requestStack;
        $this->getRequestLocale = $this->requestStack->getCurrentRequest()->getLocale();
        $this->entityManager = $entityManager;
        $this->categoryTranslationRepository = $this->entityManager->getRepository(CategoryTranslation::class);
        $this->categoryRepository = $this->entityManager->getRepository(Category::class);
        $this->urlHelper = $urlHelper;
    }

    /**
     * @Route(
     *     "/{_locale}/category/{catID}-{catName}/",
     *     name="category",
     *     methods={"GET"},
     *     requirements={
     *         "_locale": "%app_locales%",
     *     }
     * )
     */
    public function index(int $catID): Response
    {
        $category = $this->categoryRepository->findOneBy(['id' => $catID]);
        if(!$category) {
            throw $this->createNotFoundException('The category does not exist');
        }

        $paginatedMyCollections = $this->paginator->paginate(
            $category->getMyCollections(),
            $this->requestStack->getCurrentRequest()->query->getInt('page', 1),
            $this->requestStack->getCurrentRequest()->query->getInt('limit', 9)
        );

        return $this->render('category/index.html.twig', [
            'categories' => $this->categoryTranslationRepository->findBy(['locale' => $this->getRequestLocale]),
            'category' => $category->translate($this->getRequestLocale),
            'myCollections' => $paginatedMyCollections
        ]);
    }

    /**
     * @Route(
     *     "/{_locale}/category/{catID}-{catName}/export/",
     *     name="category_export",
     *     methods={"GET"},
     *     requirements={
     *         "_locale": "%app_locales%",
     *     }
     * )
     */

    public function export(int $catID, string $catName): BinaryFileResponse
    {
        $category = $this->categoryRepository->findOneBy(['id' => $catID]);
        if(!$category) {
            throw $this->createNotFoundException('The category does not exist');
        }

        $collections = $category->getMyCollections();
        if(!$collections) {
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
        foreach ($collections as $collection) {
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
        $writer->save('exports/'.$catID.'-'.$catName.'.xlsx');
        $response = new BinaryFileResponse('exports/'.$catID.'-'.$catName.'.xlsx');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$catID.'-'.$catName.'.xlsx');
        return $response;
    }
}
