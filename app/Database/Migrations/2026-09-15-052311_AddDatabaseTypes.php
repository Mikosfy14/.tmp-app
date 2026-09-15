<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDatabaseTypes extends Migration
{
    public function up()
    {
        // 1. Create table database_types if not exists
        $tableExists = $this->db->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'database_types'")->getRow();
        if (!$tableExists) {
            $this->db->query("CREATE TABLE [dbo].[database_types](
                [id] [int] IDENTITY(1,1) NOT NULL,
                [type_name] [varchar](100) NOT NULL,
                [description] [varchar](250) NULL,
                [sort_order] [int] NULL,
                [is_active] [bit] NULL DEFAULT ((1)),
                CONSTRAINT [PK_database_types] PRIMARY KEY CLUSTERED ([id] ASC),
                CONSTRAINT [UQ_database_types_type_name] UNIQUE NONCLUSTERED ([type_name] ASC)
            )");
        }

        // 2. Seed initial standard options
        $options = [
            ['type_name' => 'Microsoft SQL Server', 'description' => 'Microsoft SQL Server enterprise relational database', 'sort_order' => 1],
            ['type_name' => 'MySQL', 'description' => 'Oracle MySQL open-source relational database', 'sort_order' => 2],
            ['type_name' => 'PostgreSQL', 'description' => 'PostgreSQL advanced open-source object-relational database', 'sort_order' => 3],
            ['type_name' => 'Oracle Database', 'description' => 'Oracle Database enterprise relational database management system', 'sort_order' => 4],
            ['type_name' => 'MongoDB', 'description' => 'MongoDB NoSQL document-oriented database', 'sort_order' => 5],
            ['type_name' => 'SQLite', 'description' => 'SQLite embedded lightweight serverless SQL database engine', 'sort_order' => 6],
            ['type_name' => 'Redis', 'description' => 'Redis in-memory key-value data structure store', 'sort_order' => 7],
            ['type_name' => 'MariaDB', 'description' => 'MariaDB community-developed fork of MySQL', 'sort_order' => 8],
        ];

        foreach ($options as $opt) {
            $existing = $this->db->query("SELECT id FROM [dbo].[database_types] WHERE type_name = ?", [$opt['type_name']])->getRow();
            if (!$existing) {
                $this->db->query(
                    "INSERT INTO [dbo].[database_types] (type_name, description, sort_order, is_active) VALUES (?, ?, ?, 1)",
                    [$opt['type_name'], $opt['description'], $opt['sort_order']]
                );
            }
        }

        // 3. Add database_type_id to projects
        $colExists = $this->db->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'database_type_id'")->getRow();
        if (!$colExists) {
            $this->db->query("ALTER TABLE [dbo].[projects] ADD [database_type_id] [int] NULL");

            // Update existing projects to default option (1 = Microsoft SQL Server)
            $this->db->query("UPDATE [dbo].[projects] SET [database_type_id] = 1 WHERE [database_type_id] IS NULL");

            // Add foreign key constraint
            $this->db->query("ALTER TABLE [dbo].[projects] WITH CHECK ADD CONSTRAINT [FK_projects_database_types] FOREIGN KEY([database_type_id]) REFERENCES [dbo].[database_types] ([id])");
            $this->db->query("ALTER TABLE [dbo].[projects] CHECK CONSTRAINT [FK_projects_database_types]");
        }
    }

    public function down()
    {
        $fkExists = $this->db->query("SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_projects_database_types'")->getRow();
        if ($fkExists) {
            $this->db->query("ALTER TABLE [dbo].[projects] DROP CONSTRAINT [FK_projects_database_types]");
        }

        $colExists = $this->db->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'database_type_id'")->getRow();
        if ($colExists) {
            $this->db->query("ALTER TABLE [dbo].[projects] DROP COLUMN [database_type_id]");
        }

        $tableExists = $this->db->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'database_types'")->getRow();
        if ($tableExists) {
            $this->db->query("DROP TABLE [dbo].[database_types]");
        }
    }
}
