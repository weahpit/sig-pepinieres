<?php

namespace App\Form;

use App\Entity\Agent;
use App\Entity\Pepiniere;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PepiniereType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codePepiniere', TextType::class, ['label' => 'Code de la pépinière'])
            ->add('nomPepiniere', TextType::class, ['label' => 'Nom de la pépinière'])
            ->add('region', TextType::class, ['label' => 'Région', 'required' => false])
            ->add('departement', TextType::class, ['label' => 'Département', 'required' => false])
            ->add('sousPrefecture', TextType::class, ['label' => 'Sous-préfecture', 'required' => false])
            ->add('village', TextType::class, ['label' => 'Village', 'required' => false])
            ->add('latitude', NumberType::class, ['label' => 'Latitude', 'required' => false, 'scale' => 7])
            ->add('longitude', NumberType::class, ['label' => 'Longitude', 'required' => false, 'scale' => 7])
            ->add('superficieM2', NumberType::class, ['label' => 'Superficie (m²)', 'required' => false, 'scale' => 2])
            ->add('organismeGestionnaire', TextType::class, ['label' => 'Organisme gestionnaire', 'required' => false])
            ->add('capaciteProductionAn', IntegerType::class, ['label' => 'Capacité de production annuelle', 'required' => false])
            ->add('dateCreation', DateType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('responsable', EntityType::class, [
                'class' => Agent::class,
                'choice_label' => 'nom',
                'label' => 'Responsable',
                'required' => false,
                'placeholder' => '-- Aucun --',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Pepiniere::class]);
    }
}
