<?php

namespace App\Form;

use App\Entity\Attribute;
use App\Entity\Category;
use App\Entity\CategoryTranslation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddAtributeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'app.addAttributeName',
            ])
            ->add('category', EntityType::class, [
                'label' => 'app.addAttributeChooseEntity',
                'class' => Category::class,
                'choice_label' => function ($category) {
                    return $category->translate('en')->getName();
                },
            ])
            ->add(
                'button',
                SubmitType::class,
                [
                    'label' => 'app.addAttributeSubmit',
                ]
            );
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Attribute::class,
            'translation_domain' => 'app'
        ]);
    }
}
