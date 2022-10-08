<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\MyCollection;
use App\Entity\MyCollectionAttribute;
use App\Form\MyCollectionAttributeFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class CollectionController extends AbstractController
{
    private ObjectRepository $collectionRepository;
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;
    private ObjectRepository $categoryRepository;
    private Filesystem $filesystem;

    public function __construct
    (
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        Filesystem $filesystem
    )
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->collectionRepository = $this->entityManager->getRepository(MyCollection::class);
        $this->categoryRepository = $this->entityManager->getRepository(Category::class);
        $this->filesystem = $filesystem;
    }

    /**
     * @Route(
     *     "/{_locale}/collection/{slug}/",
     *     name="collection",
     *     methods={"GET", "POST"},
     *     requirements={
     *         "_locale": "%app_locales%",
     *     }
     * )
     */
    public function index(string $slug): Response
    {
        $collection = $this->collectionRepository->findOneBy(['slug' => $slug]);
        $category = $collection->getCategory()->translate($this->requestStack->getCurrentRequest()->getLocale())->getName();
        $categoryId = $collection->getCategory()->getId();
        $similarCollections = $this->categoryRepository->findOneBy(['id' => $categoryId])->getMyCollections();
        foreach($similarCollections as $k => $item) {
            if ($item->getSlug() == $slug) {
                unset($similarCollections[$k]);
                break;
            }
        }
        $similarCollections = $similarCollections->toArray();
        shuffle($similarCollections);

        $myCollectionAttribute = new MyCollectionAttribute();
        $formSetAttribute = $this->createForm(MyCollectionAttributeFormType::class, $myCollectionAttribute, ['id' => $collection->getCategory()->getId()]);
        $formSetAttribute->handleRequest($this->requestStack->getCurrentRequest());

        if($formSetAttribute->isSubmitted() && $formSetAttribute->isValid()) {
            $exist = false;
            foreach ($collection->getMyCollectionAttributes() as $isMyCollectionAttribute) {
                if($isMyCollectionAttribute->getAttribute()->getID() == $formSetAttribute->get('attribute')->getData()->getID()) {
                    $isMyCollectionAttribute->setValue($formSetAttribute->get('value')->getData());
                    $this->entityManager->persist($isMyCollectionAttribute);
                    $exist = true;
                }
            }
            if($exist == false) {
                $myCollectionAttribute->setAttribute($formSetAttribute->get('attribute')->getData());
                $myCollectionAttribute->setMyCollection($collection);
                $myCollectionAttribute->setValue($formSetAttribute->get('value')->getData());
                $this->entityManager->persist($myCollectionAttribute);
            }
            $this->entityManager->flush();
            return $this->redirectToRoute('collection', ['slug' => $slug]);
        }

        return $this->render('collection/index.html.twig', [
            'collection' => $collection,
            'category' => $category,
            'formSetAttribute' => $formSetAttribute->createView(),
            'similarCollections' => $similarCollections
        ]);
    }

    /**
     * @Route(
     *     "/{_locale}/collection/{slug}/delete/",
     *     name="collection_delete",
     *     methods={"GET"},
     *     requirements={
     *         "_locale": "%app_locales%",
     *     }
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(string $slug): RedirectResponse
    {
        $collection = $this->collectionRepository->findOneBy(['slug' => $slug]);

        foreach ($collection->getMyCollectionAttributes() as $attribute) {
            $this->entityManager->remove($attribute);
        }
        if(!empty($collection->getImage())) {
            $this->filesystem->remove('assets/img/' . $collection->getImage());
        }

        $this->entityManager->remove($collection);
        $this->entityManager->flush();

        return $this->redirectToRoute('index');
    }

    /**
     * @Route(
     *     "/{_locale}/collection/{slug}/export/",
     *     name="collection_export",
     *     methods={"GET"},
     *     requirements={
     *         "_locale": "%app_locales%",
     *     }
     * )
     * @throws Exception
     */

    public function export(string $slug): BinaryFileResponse
    {
        $collection = $this->collectionRepository->findOneBy(['slug' => $slug]);
        $attributes = $collection->getMyCollectionAttributes()->toArray();

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

        $drawing = new Drawing();
        $drawing->setName('Image');
        $drawing->setDescription('Image');
        $drawing->setPath('assets/img/'.$collection->getImage());
        $drawing->setCoordinates('A2');
        $drawing->setWidthAndHeight(100,150);
        $drawing->setResizeProportional(true);
        $drawing->setWorksheet($sheet);

        $sheet->setCellValue('B2', $collection->getName());
        $sheet->setCellValue('C2', $collection->getDescription());

        $attr = '';
        foreach ($attributes as $attribute) {
            $attr .= $attribute->getAttribute()->getName().': '.$attribute->getValue().PHP_EOL;
        }
        $sheet->setCellValue('D2', $attr);

        $writer = new Xlsx($spreadsheet);
        $writer->save('exports/'.$slug.'.xlsx');
        $response = new BinaryFileResponse('exports/'.$slug.'.xlsx');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$slug.'.xlsx');
        return $response;
    }
}
