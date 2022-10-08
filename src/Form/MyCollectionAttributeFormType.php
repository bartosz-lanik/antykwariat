<?php

namespace App\Form;

use App\Entity\Attribute;
use App\Entity\Category;
use App\Entity\MyCollectionAttribute;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyCollectionAttributeFormType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    /**
     * MyCollectionAttributeFormType constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('attribute',EntityType::class,
            [
                'label' => 'app.addAttributeToObjectAttributeName',
                'class' => Attribute::class,
                'choices' => $this->entityManager->getRepository(Category::class)->findOneBy(['id' => $options['id']])->getAttributes(),
                'choice_label' => 'name',
                'multiple' => false
            ])
            ->add('value', TextType::class, [
                'label' => 'app.addAttributeToObjectValue'
            ])
            ->add(
                'button',
                SubmitType::class,
                [
                    'label' => 'app.addAttributeToObjectSubmit'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyCollectionAttribute::class,
            'translation_domain' => 'app',
            $resolver->setRequired([
                'id'
            ])
        ]);
    }
}
