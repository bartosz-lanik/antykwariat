<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\MyCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AddObjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'app.addObjectName',
            ])
            ->add('description', TextType::class, [
                'label' => 'app.addObjectDescription',
            ])
            ->add('category', EntityType::class, [
                'label' => 'app.addObjectChooseEntity',
                'class' => Category::class,
                'choice_label' => function ($category) {
                    return $category->translate('en')->getName();
                },
            ])
            ->add(
                'image',
                FileType::class,
                [
                    'label' => 'app.addObjectChooseImage',
                    'multiple' => false,
                    'required' => false,
                    'constraints' => [
                        new File(
                            [
                                'maxSize' => '2M',
                                'mimeTypes' => [
                                    'image/*'
                                ],
                                'mimeTypesMessage' => 'Obsługiwany format pliku musi być obrazem'
                            ]
                        )
                    ]
                ]
            )
            ->add(
                'button',
                SubmitType::class,
                [
                    'label' => 'app.addObjectSubmit',
                ]
            );
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyCollection::class,
            'translation_domain' => 'app'
        ]);
    }
}