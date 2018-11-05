<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


class Version20180809220705 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $passForAn='$2y$13$Da7aHIBE5olpFabP/Z19ZOco8OjJkZ1oZd35ae1B44sVv5qBOyHvq';
        $passForUser='$2y$13$HiQ91oh42ZC0Hz4jM9DQgefFuFVsEnPGz7Nw/7DvCvFY5.m3zdNyy';
        $this->addSql("
		    INSERT INTO `user_types` (`id`, `type`) VALUES
            (2, 'ROLE_ADMIN'),
            (1, 'ROLE_USER');
            INSERT INTO `users` (`id`, `role`, `username`, `password`) VALUES
            (1, 1, 'an21', '$passForAn'),
            (2, 1, 'user', '$passForUser');
            INSERT INTO `post` (`id`, `user_id`, `title`, `description`, `date_created`, `date_updated`) VALUES
            (1, 1, 'New', 'description', '2018-08-09 21:25:38', NULL),
            (2, 1, 'sometitle', 'somedescr', '2018-08-09 21:33:19', NULL),
            (3, 2, 'sometitlesome', 'somedescrsome', '2018-08-09 21:47:29', NULL),
            (6, 2, 'anothertitle', 'anotherdescr', '2018-08-09 23:32:29', NULL),
            (7, 1, 'tester', 'test', '2018-08-09 23:47:28', NULL);         
            ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("TRUNCATE TABLE `post`; TRUNCATE TABLE `users`; TRUNCATE TABLE `user_types`");

    }
}
