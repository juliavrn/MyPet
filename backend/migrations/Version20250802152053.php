<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250802152053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE health_record ADD signs_of_illness TINYINT(1) DEFAULT NULL, ADD fever TINYINT(1) DEFAULT NULL, ADD vomiting TINYINT(1) DEFAULT NULL, ADD limping TINYINT(1) DEFAULT NULL, ADD other_health_observations LONGTEXT DEFAULT NULL, ADD ate_all_meals TINYINT(1) DEFAULT NULL, ADD appetite VARCHAR(20) DEFAULT NULL, ADD water_intake VARCHAR(20) DEFAULT NULL, ADD foods_given LONGTEXT DEFAULT NULL, ADD treats_given TINYINT(1) DEFAULT NULL, ADD brushing_done TINYINT(1) DEFAULT NULL, ADD bath_or_cleaning TINYINT(1) DEFAULT NULL, ADD nails_checked TINYINT(1) DEFAULT NULL, ADD ears_cleaned TINYINT(1) DEFAULT NULL, ADD coat_condition VARCHAR(50) DEFAULT NULL, ADD walking_time INT DEFAULT NULL, ADD activity_type VARCHAR(50) DEFAULT NULL, ADD energy_level VARCHAR(20) DEFAULT NULL, ADD stressed TINYINT(1) DEFAULT NULL, ADD unusual_signs TINYINT(1) DEFAULT NULL, ADD mood_changes TINYINT(1) DEFAULT NULL, ADD behavior_details LONGTEXT DEFAULT NULL, ADD obedience_exercises TINYINT(1) DEFAULT NULL, ADD met_other_animals TINYINT(1) DEFAULT NULL, ADD positive_human_interaction TINYINT(1) DEFAULT NULL, ADD new_learnings LONGTEXT DEFAULT NULL, ADD living_space_cleaned TINYINT(1) DEFAULT NULL, ADD correct_temperature TINYINT(1) DEFAULT NULL, ADD environment_changed TINYINT(1) DEFAULT NULL, ADD environment_issues LONGTEXT DEFAULT NULL, ADD medication_given TINYINT(1) DEFAULT NULL, ADD supplements_given TINYINT(1) DEFAULT NULL, ADD antiparasitic_treatment TINYINT(1) DEFAULT NULL, ADD other_specific_care LONGTEXT DEFAULT NULL, ADD worked_objective LONGTEXT DEFAULT NULL, ADD observed_progress LONGTEXT DEFAULT NULL, ADD necessary_adjustments LONGTEXT DEFAULT NULL, ADD general_notes LONGTEXT DEFAULT NULL, DROP type, DROP description, DROP veterinarian, DROP cost, CHANGE date date DATE NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE prescription observed_injuries LONGTEXT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE health_record ADD type VARCHAR(100) NOT NULL, ADD description LONGTEXT NOT NULL, ADD veterinarian VARCHAR(255) DEFAULT NULL, ADD prescription LONGTEXT DEFAULT NULL, ADD cost NUMERIC(10, 2) DEFAULT NULL, DROP signs_of_illness, DROP fever, DROP vomiting, DROP limping, DROP observed_injuries, DROP other_health_observations, DROP ate_all_meals, DROP appetite, DROP water_intake, DROP foods_given, DROP treats_given, DROP brushing_done, DROP bath_or_cleaning, DROP nails_checked, DROP ears_cleaned, DROP coat_condition, DROP walking_time, DROP activity_type, DROP energy_level, DROP stressed, DROP unusual_signs, DROP mood_changes, DROP behavior_details, DROP obedience_exercises, DROP met_other_animals, DROP positive_human_interaction, DROP new_learnings, DROP living_space_cleaned, DROP correct_temperature, DROP environment_changed, DROP environment_issues, DROP medication_given, DROP supplements_given, DROP antiparasitic_treatment, DROP other_specific_care, DROP worked_objective, DROP observed_progress, DROP necessary_adjustments, DROP general_notes, CHANGE date date DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }
}
