<?php

namespace App\Form;

use App\Entity\Agent;
use App\Entity\Lot;
use App\Entity\SuiviCroissance;
use App\Enum\EtatSanitaire;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuiviCroissanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lot', EntityType::class, [
                'class' => Lot::class, 'choice_label' => 'numeroLot', 'label' => 'Lot',
            ])
            ->add('dateMesure', DateType::class, [
                'label' => 'Date de mesure', 'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('hauteurMoyCm', NumberType::class, [
                'label' => 'Hauteur moyenne (cm)', 'required' => false, 'scale' => 2,
            ])
            ->add('diametreColletMm', NumberType::class, [
                'label' => 'Diamètre au collet (mm)', 'required' => false, 'scale' => 2,
            ])
            ->add('nbFeuilles', IntegerType::class, ['label' => 'Nombre de feuilles', 'required' => false])
            ->add('etatSanitaire', EnumType::class, [
                'class' => EtatSanitaire::class,
                'label' => 'État sanitaire',
                'required' => false,
                'placeholder' => '-- Non renseigné --',
                'choice_label' => fn (EtatSanitaire $e) => $e->value,
            ])
            ->add('observations', TextareaType::class, [
                'label' => 'Observations', 'required' => false, 'attr' => ['rows' => 2],
            ])
            ->add('agent', EntityType::class, [
                'class' => Agent::class, 'choice_label' => 'nom', 'label' => 'Agent',
                'required' => false, 'placeholder' => '-- Aucun --',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => SuiviCroissance::class]);
    }
}
