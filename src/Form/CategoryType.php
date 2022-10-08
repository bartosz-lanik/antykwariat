<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name_PL',
                TextType::class,
                [
                    'label' => 'app.addCategoryNamePL',
                    'mapped' => false
                ]
            )
            ->add(
                'name_EN',
                TextType::class,
                [
                    'label' => 'app.addCategoryNameEN',
                    'mapped' => false
                ]
            )
            ->add(
                'button',
                SubmitType::class,
                [
                    'label' => 'app.addCategoryNameSubmit',
                ]
            );
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
            'translation_domain' => 'app'
        ]);
    }
}
