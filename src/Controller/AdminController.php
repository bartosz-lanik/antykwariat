<?php

namespace App\Controller;

use App\Entity\Attribute;
use App\Entity\Category;
use App\Entity\MyCollection;
use App\Form\AddAtributeType;
use App\Form\AddObjectType;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ObjectRepository $categoryRepository;
    private RequestStack $requestStack;
    private ObjectRepository $attributeRepository;
    private SluggerInterface $slugger;

    public function __construct
    (
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        SluggerInterface $slugger
    )
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->categoryRepository = $this->entityManager->getRepository(Category::class);
        $this->attributeRepository = $this->entityManager->getRepository(Attribute::class);
        $this->slugger = $slugger;
    }

    /**
     * @Route(
     *     "/admin/manage/",
     *     name="admin_manage_collections",
     *     methods={"GET", "POST"}
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function add_collection(): Response
    {
        $form = $this->createForm(CategoryType::class);
        $request = $this->requestStack->getCurrentRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = new Category();
            $category->translate('pl')->setName($form->get('name_PL')->getData());
            $category->translate('en')->setName($form->get('name_EN')->getData());

            $this->getDoctrine()->getManager()->persist($category);
            $category->mergeNewTranslations();
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('admin_manage_collections');
        }

        $form_attribute = $this->createForm(AddAtributeType::class);
        $form_attribute->handleRequest($request);

        if ($form_attribute->isSubmitted() && $form_attribute->isValid()) {
            $attribute = new Attribute();
            $attribute->setName($form_attribute->get('name')->getData());
            $attribute->setCategory($form_attribute->get('category')->getData());

            $this->getDoctrine()->getManager()->persist($attribute);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('admin_manage_collections');
        }

        $form_object = $this->createForm(AddObjectType::class);
        $form_object->handleRequest($request);

        if ($form_object->isSubmitted() && $form_object->isValid()) {
            $object = new MyCollection();
            $object->setName($form_object->get('name')->getData());
            $object->setSlug(strtolower($this->slugger->slug($form_object->get('name')->getData())));
            $object->setDescription($form_object->get('description')->getData());
            $object->setCategory($form_object->get('category')->getData());

            $image = $form_object->get('image')->getData();
            if($image !== null) {
                $imageFileName = md5(uniqid().rand(1,999)).'.'.$image->guessExtension();
                $image->move('assets/img/', $imageFileName);
                $object->setImage($imageFileName);
            }

            $this->getDoctrine()->getManager()->persist($object);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('admin_manage_collections');
        }

        return $this->render('admin/manageCollections.html.twig', [
            'form' => $form->createView(),
            'form_object' => $form_object->createView(),
            'form_attribute' => $form_attribute->createView(),
            'categories' => $this->categoryRepository->findAll()
        ]);
    }

    /**
     * @Route(
     *     "/admin/manage/collections/delete/{id}/",
     *     name="admin_manage_collections_delete",
     *     methods={"GET"}
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete_collection(int $id): RedirectResponse
    {
        $categories = $this->categoryRepository->findBy(['id' => $id]);
        foreach ($categories as $category) {
            foreach ($category->getAttributes() as $attribute) {
                foreach ($attribute->getMyCollectionAttributes() as $myCollectionAttribute) {
                    $this->entityManager->remove($myCollectionAttribute);
                }
                $this->entityManager->remove($attribute);
            }
            foreach ($category->getMyCollections() as $collection) {
                foreach ($collection->getMyCollectionAttributes() as $myCollectionAttribute) {
                    $this->entityManager->remove($myCollectionAttribute);
                }
                $this->entityManager->remove($collection);
            }
            $this->entityManager->remove($category);
        }
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_manage_collections');
    }

    /**
     * @Route(
     *     "/admin/manage/collections/attributes/delete/{id}/",
     *     name="admin_manage_collections_attributes_delete",
     *     methods={"GET"}
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete_attribute(int $id): RedirectResponse
    {
        $attributes = $this->attributeRepository->findBy(['id' => $id]);
        foreach ($attributes as $attribute) {
            $this->entityManager->remove($attribute);
        }
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_manage_collections');
    }
}
