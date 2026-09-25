<?php

namespace App\Form;

use App\Entity\Agent;
use App\Entity\Lot;
use App\Entity\SuiviPhytosanitaire;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuiviPhytosanitaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lot', EntityType::class, [
                'class' => Lot::class, 'choice_label' => 'numeroLot', 'label' => 'Lot',
            ])
            ->add('dateObservation', DateType::class, [
                'label' => 'Date d\'observation', 'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('maladieRavageur', TextType::class, [
                'label' => 'Maladie / Ravageur', 'required' => false,
            ])
            ->add('symptomes', TextareaType::class, [
                'label' => 'Symptômes', 'required' => false, 'attr' => ['rows' => 2],
            ])
            ->add('traitementApplique', TextareaType::class, [
                'label' => 'Traitement appliqué', 'required' => false, 'attr' => ['rows' => 2],
            ])
            ->add('produit', TextType::class, ['label' => 'Produit', 'required' => false])
            ->add('resultat', TextType::class, ['label' => 'Résultat', 'required' => false])
            ->add('agent', EntityType::class, [
                'class' => Agent::class, 'choice_label' => 'nom', 'label' => 'Agent',
                'required' => false, 'placeholder' => '-- Aucun --',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => SuiviPhytosanitaire::class]);
    }
}
