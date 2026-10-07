<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute la colonne contour_geojson (parcelle / délimitation Kobo) à la table pepiniere.
 */
final class Version20261008021500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du contour (parcelle) GeoJSON sur pepiniere pour le tracé des parcelles sur la carte';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pepiniere ADD contour_geojson JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pepiniere DROP contour_geojson');
    }
}
