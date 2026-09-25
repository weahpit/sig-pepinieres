<?php

namespace App\Form;

use App\Entity\Espece;
use App\Entity\Lot;
use App\Entity\LotSemence;
use App\Entity\Pepiniere;
use App\Entity\Planche;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroLot', TextType::class, ['label' => 'Numéro du lot'])
            ->add('pepiniere', EntityType::class, [
                'class' => Pepiniere::class, 'choice_label' => 'nomPepiniere', 'label' => 'Pépinière',
            ])
            ->add('espece', EntityType::class, [
                'class' => Espece::class, 'choice_label' => 'nomCommun', 'label' => 'Espèce',
            ])
            ->add('planche', EntityType::class, [
                'class' => Planche::class, 'choice_label' => 'numeroPlanche', 'label' => 'Planche',
                'required' => false, 'placeholder' => '-- Aucune --',
            ])
            ->add('lotSemence', EntityType::class, [
                'class' => LotSemence::class, 'choice_label' => 'numeroLot', 'label' => 'Lot de semences',
                'required' => false, 'placeholder' => '-- Aucun --',
            ])
            ->add('dateSemis', DateType::class, [
                'label' => 'Date de semis', 'widget' => 'single_text',
                'input' => 'datetime_immutable', 'required' => false,
            ])
            ->add('nbGrainesSemees', IntegerType::class, ['label' => 'Nombre de graines semées', 'required' => false])
            ->add('dateGermination', DateType::class, [
                'label' => 'Date de germination', 'widget' => 'single_text',
                'input' => 'datetime_immutable', 'required' => false,
            ])
            ->add('nbPlantsLeves', IntegerType::class, ['label' => 'Nombre de plants levés', 'required' => false])
            ->add('dateSortie', DateType::class, [
                'label' => 'Date de sortie', 'widget' => 'single_text',
                'input' => 'datetime_immutable', 'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Lot::class]);
    }
}
