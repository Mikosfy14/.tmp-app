-- =========================================================
-- DATABASE REVISION SCRIPT
-- Project Dashboard App
-- Engine: Microsoft SQL Server
-- Date: 2026-08-18
--
-- Scope:
-- 1. Add project_status lookup table.
-- 2. Add criticality_recovery lookup table.
-- 3. Add project_files table for SQL Server stored attachments.
-- 4. Add FK columns:
--    - projects.project_status_id
--    - applications.criticality_recovery_id
-- 5. Backfill FK columns from legacy varchar columns:
--    - projects.status
--    - applications.criticality_recovery
--
-- Notes:
-- - This script intentionally DOES NOT create a pivot table.
-- - projects.assigned_to remains a comma-separated string of user IDs,
--   e.g. '2,5,8'. The first ID remains the project creator/inputter.
-- - Legacy columns are kept during transition to avoid breaking current code.
--   Drop them only after the application code fully uses the new FK columns.
-- =========================================================

SET ANSI_NULLS ON;
GO

SET QUOTED_IDENTIFIER ON;
GO

BEGIN TRY
    BEGIN TRANSACTION;

    -- =====================================================
    -- 1. MASTER TABLE: project_status
    -- =====================================================
    IF OBJECT_ID('dbo.project_status', 'U') IS NULL
    BEGIN
        CREATE TABLE dbo.project_status (
            id INT IDENTITY(1,1) NOT NULL,
            status_name VARCHAR(100) NOT NULL,
            description VARCHAR(250) NULL,
            sort_order INT NULL,
            is_active BIT NULL CONSTRAINT DF_project_status_is_active DEFAULT ((1)),
            CONSTRAINT PK_project_status PRIMARY KEY CLUSTERED (id ASC),
            CONSTRAINT UQ_project_status_status_name UNIQUE NONCLUSTERED (status_name ASC)
        );
    END;

    -- Seed follows the approved standard SDLC project flow.
    INSERT INTO dbo.project_status (status_name, description, sort_order)
    SELECT seed.status_name, seed.description, seed.sort_order
    FROM (VALUES
        ('Planning', 'Project planning and initial preparation', 10),
        ('Defining', 'Requirement definition and scope clarification', 20),
        ('Designing', 'Solution and technical design phase', 30),
        ('Building', 'Development or implementation phase', 40),
        ('Testing', 'Testing, validation, SIT, or UAT phase', 50),
        ('Deployment', 'Deployment or production promotion phase', 60)
    ) AS seed(status_name, description, sort_order)
    WHERE NOT EXISTS (
        SELECT 1
        FROM dbo.project_status ps
        WHERE ps.status_name = seed.status_name
    );

    -- =====================================================
    -- 2. MASTER TABLE: criticality_recovery
    -- =====================================================
    IF OBJECT_ID('dbo.criticality_recovery', 'U') IS NULL
    BEGIN
        CREATE TABLE dbo.criticality_recovery (
            id INT IDENTITY(1,1) NOT NULL,
            criticality_name VARCHAR(100) NOT NULL,
            description VARCHAR(250) NULL,
            sort_order INT NULL,
            is_active BIT NULL CONSTRAINT DF_criticality_recovery_is_active DEFAULT ((1)),
            CONSTRAINT PK_criticality_recovery PRIMARY KEY CLUSTERED (id ASC),
            CONSTRAINT UQ_criticality_recovery_name UNIQUE NONCLUSTERED (criticality_name ASC)
        );
    END;

    INSERT INTO dbo.criticality_recovery (criticality_name, description, sort_order)
    SELECT seed.criticality_name, seed.description, seed.sort_order
    FROM (VALUES
        ('Criticality 1', 'Critical Functions', 10),
        ('Criticality 2', 'Essential Functions', 20),
        ('Criticality 3', 'Necessary Functions', 30),
        ('Criticality 4', 'Desirable Functions', 40)
    ) AS seed(criticality_name, description, sort_order)
    WHERE NOT EXISTS (
        SELECT 1
        FROM dbo.criticality_recovery cr
        WHERE cr.criticality_name = seed.criticality_name   
    );

    -- =====================================================
    -- 3. ALTER projects: add project_status_id
    -- =====================================================
    IF COL_LENGTH('dbo.projects', 'project_status_id') IS NULL
    BEGIN
        ALTER TABLE dbo.projects ADD project_status_id INT NULL;
    END;

    UPDATE p
        SET project_status_id = ps.id
    FROM dbo.projects p
    INNER JOIN dbo.project_status ps
        ON LOWER(LTRIM(RTRIM(ps.status_name))) = LOWER(LTRIM(RTRIM(p.status)))
    WHERE p.project_status_id IS NULL
      AND p.status IS NOT NULL;

    IF OBJECT_ID('dbo.FK_projects_project_status', 'F') IS NULL
    BEGIN
        ALTER TABLE dbo.projects WITH CHECK ADD CONSTRAINT FK_projects_project_status
            FOREIGN KEY (project_status_id)
            REFERENCES dbo.project_status(id);

        ALTER TABLE dbo.projects CHECK CONSTRAINT FK_projects_project_status;
    END;

    -- Helpful indexes for future joins/filtering.
    IF NOT EXISTS (
        SELECT 1
        FROM sys.indexes
        WHERE name = 'IX_projects_project_status_id'
          AND object_id = OBJECT_ID('dbo.projects')
    )
    BEGIN
        CREATE NONCLUSTERED INDEX IX_projects_project_status_id
            ON dbo.projects(project_status_id);
    END;

    -- =====================================================
    -- 4. ALTER applications: add criticality_recovery_id
    -- =====================================================
    IF COL_LENGTH('dbo.applications', 'criticality_recovery_id') IS NULL
    BEGIN
        ALTER TABLE dbo.applications ADD criticality_recovery_id INT NULL;
    END;

    UPDATE a
        SET criticality_recovery_id = cr.id
    FROM dbo.applications a
    INNER JOIN dbo.criticality_recovery cr
        ON LOWER(LTRIM(RTRIM(cr.criticality_name))) = LOWER(LTRIM(RTRIM(a.criticality_recovery)))
    WHERE a.criticality_recovery_id IS NULL
      AND a.criticality_recovery IS NOT NULL;

    IF OBJECT_ID('dbo.FK_applications_criticality_recovery', 'F') IS NULL
    BEGIN
        ALTER TABLE dbo.applications WITH CHECK ADD CONSTRAINT FK_applications_criticality_recovery
            FOREIGN KEY (criticality_recovery_id)
            REFERENCES dbo.criticality_recovery(id);

        ALTER TABLE dbo.applications CHECK CONSTRAINT FK_applications_criticality_recovery;
    END;

    IF NOT EXISTS (
        SELECT 1
        FROM sys.indexes
        WHERE name = 'IX_applications_criticality_recovery_id'
          AND object_id = OBJECT_ID('dbo.applications')
    )
    BEGIN
        CREATE NONCLUSTERED INDEX IX_applications_criticality_recovery_id
            ON dbo.applications(criticality_recovery_id);
    END;

    -- =====================================================
    -- 5. TABLE: project_files
    -- =====================================================
    IF OBJECT_ID('dbo.project_files', 'U') IS NULL
    BEGIN
        CREATE TABLE dbo.project_files (
            id INT IDENTITY(1,1) NOT NULL,
            project_id INT NOT NULL,
            original_name NVARCHAR(255) NOT NULL,
            mime_type VARCHAR(150) NOT NULL,
            file_extension VARCHAR(20) NOT NULL,
            file_size INT NOT NULL,
            file_data VARBINARY(MAX) NOT NULL,
            uploaded_by INT NULL,
            created_at DATETIME NULL CONSTRAINT DF_project_files_created_at DEFAULT (GETDATE()),
            CONSTRAINT PK_project_files PRIMARY KEY CLUSTERED (id ASC),
            CONSTRAINT FK_project_files_projects FOREIGN KEY (project_id)
                REFERENCES dbo.projects(id)
                ON DELETE CASCADE,
            CONSTRAINT FK_project_files_users FOREIGN KEY (uploaded_by)
                REFERENCES dbo.users(id)
                ON DELETE SET NULL,
            CONSTRAINT CK_project_files_file_size CHECK (file_size > 0 AND file_size <= 5242880),
            CONSTRAINT CK_project_files_extension CHECK (
                LOWER(file_extension) IN ('pdf', 'doc', 'docx', 'xls', 'xlsx')
            )
        );
    END;

    IF NOT EXISTS (
        SELECT 1
        FROM sys.indexes
        WHERE name = 'IX_project_files_project_id'
          AND object_id = OBJECT_ID('dbo.project_files')
    )
    BEGIN
        CREATE NONCLUSTERED INDEX IX_project_files_project_id
            ON dbo.project_files(project_id);
    END;

    IF NOT EXISTS (
        SELECT 1
        FROM sys.indexes
        WHERE name = 'IX_project_files_uploaded_by'
          AND object_id = OBJECT_ID('dbo.project_files')
    )
    BEGIN
        CREATE NONCLUSTERED INDEX IX_project_files_uploaded_by
            ON dbo.project_files(uploaded_by);
    END;

    COMMIT TRANSACTION;
END TRY
BEGIN CATCH
    IF @@TRANCOUNT > 0
        ROLLBACK TRANSACTION;

    THROW;
END CATCH;
GO

-- =========================================================
-- POST-MIGRATION VALIDATION QUERIES
-- =========================================================
SELECT id, status_name, sort_order, is_active
FROM dbo.project_status
ORDER BY sort_order, id;

SELECT id, criticality_name, description, sort_order, is_active
FROM dbo.criticality_recovery
ORDER BY sort_order, id;

SELECT
    p.id,
    p.project_code,
    p.status AS legacy_status,
    p.project_status_id,
    ps.status_name
FROM dbo.projects p
LEFT JOIN dbo.project_status ps ON ps.id = p.project_status_id
ORDER BY p.id;

SELECT
    a.id,
    a.app_component,
    a.criticality_recovery AS legacy_criticality_recovery,
    a.criticality_recovery_id,
    cr.criticality_name
FROM dbo.applications a
LEFT JOIN dbo.criticality_recovery cr ON cr.id = a.criticality_recovery_id
ORDER BY a.id;
GO

-- =========================================================
-- CLEANUP PLAN AFTER APPLICATION CODE MIGRATION
-- =========================================================
-- Run only after the application fully reads/writes:
-- - projects.project_status_id instead of projects.status
-- - applications.criticality_recovery_id instead of applications.criticality_recovery
--
-- ALTER TABLE dbo.projects DROP COLUMN status;
-- ALTER TABLE dbo.applications DROP COLUMN criticality_recovery;
-- GO
