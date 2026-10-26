SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `DocumentTypes`;
DROP TABLE IF EXISTS `PDLNDocuments`;
DROP TABLE IF EXISTS `PDLNs`;
DROP TABLE IF EXISTS `Reports`;
DROP TABLE IF EXISTS `Users`;
DROP TABLE IF EXISTS `Outbounds`;
DROP TABLE IF EXISTS `Tickets`;
DROP TABLE IF EXISTS `TicketDocuments`;
DROP TABLE IF EXISTS `Inbounds`;
DROP TABLE IF EXISTS `PDLNParticapants`;
DROP TABLE IF EXISTS `Departments`;
DROP TABLE IF EXISTS `StudyPrograms`;
DROP TABLE IF EXISTS `Programs`;
DROP TABLE IF EXISTS `DocumentTemplates`;
DROP TABLE IF EXISTS `FAQs`;
DROP TABLE IF EXISTS `InboundPrograms`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `DocumentTypes` (
    `DocTypeId` VARCHAR(36) NOT NULL DEFAULT (UUID()),
    `Name` VARCHAR(100) NOT NULL,
    `MaxSize` INT NOT NULL,
    `IsMandatory` TINYINT NOT NULL,
    PRIMARY KEY (`DocTypeId`)
);

CREATE TABLE `PDLNDocuments` (
    `PDLNId` VARCHAR(36) NOT NULL,
    `DocTypeId` VARCHAR(36) NOT NULL,
    `Path` VARCHAR(100) NOT NULL
);

CREATE TABLE `PDLNs` (
    `PDLNId` VARCHAR(36) NOT NULL DEFAULT (UUID()),
    `UserId` VARCHAR(36) NOT NULL,
    `ActivityName` TEXT NOT NULL,
    `ActivityType` VARCHAR(20) NOT NULL,
    `Status` ENUM('PENDING', 'VERIFICATION', 'REVISION', 'SRIKANDI', 'ACCEPTED', 'CANCELLED', 'DONE') NOT NULL,
    `DestInstitution` VARCHAR(100) NOT NULL,
    `DestCity` VARCHAR(100) NOT NULL,
    `DestCountry` VARCHAR(100) NOT NULL,
    `FollowUpPlan` TEXT NOT NULL,
    `StartDate` DATE NOT NULL,
    `EndDate` DATE NOT NULL,
    `SPSetneg` VARCHAR(100),
    PRIMARY KEY (`PDLNId`)
);

CREATE TABLE `Reports` (
    `PDLNId` VARCHAR(36) NOT NULL,
    `FollowUp` TEXT NOT NULL,
    `ImpactRecommendation` TEXT NOT NULL,
    `CostRealization` DECIMAL(10,2) NOT NULL,
    `ReportFile` VARCHAR(100) NOT NULL
);

CREATE TABLE `Users` (
    `UserId` VARCHAR(36) NOT NULL DEFAULT (UUID()),
    `Email` VARCHAR(128) NOT NULL,
    `Password` VARCHAR(100) NOT NULL,
    `Phone` VARCHAR(15) NOT NULL,
    `Name` VARCHAR(155) NOT NULL,
    `Birthdate` DATE NOT NULL,
    `BirthLocation` VARCHAR(128) NOT NULL,
    `NIK` VARCHAR(16) NOT NULL,
    `PasporNum` VARCHAR(9) NOT NULL,
    `KTP` VARCHAR(100),
    `Paspor` VARCHAR(100),
    `Address` TEXT NOT NULL,
    `Role` ENUM('SUPERADMIN', 'ADMIN', 'STRUKTURAL', 'DOSEN', 'MAHASISWA') NOT NULL DEFAULT 'MAHASISWA',
    `NIM` VARCHAR(10),
    `NIP` VARCHAR(18),
    `Position` VARCHAR(100),
    `AdmissionDate` DATE,
    `DepartmentId` VARCHAR(36),
    `StudyProgramId` VARCHAR(36),
    `ProgramId` VARCHAR(36),
    PRIMARY KEY (`UserId`),
    UNIQUE (`Email`),
    UNIQUE (`Phone`),
    UNIQUE (`NIK`),
    UNIQUE (`PasporNum`)
);

CREATE TABLE `Outbounds` (
    `OutboundId` VARCHAR(36) NOT NULL DEFAULT (UUID()),
    `UserId` VARCHAR(36) NOT NULL,
    `Status` ENUM('PENDING', 'VERIFICATION', 'REVISION', 'SRIKANDI', 'ACCEPTED', 'CANCELLED', 'DONE') NOT NULL,
    `AcademicYear` INT NOT NULL,
    `Semester` INT NOT NULL,
    `LetterType` ENUM('ENROLLMENT', 'VISA_RECOMMENDATION', 'DIRECTOR_RECOMMENDATION') NOT NULL,
    `Program` VARCHAR(100),
    `GoAs` VARCHAR(100),
    `DestInstituion` VARCHAR(100),
    `DestCountry` VARCHAR(100),
    `StartDate` DATE,
    `EndDate` DATE,
    `DecideBy` VARCHAR(36),
    `DecidedAt` TIMESTAMP,
    `OutboundLetterPath` VARCHAR(100),
    PRIMARY KEY (`OutboundId`)
);

CREATE TABLE `Tickets` (
    `TicketId` VARCHAR(36) NOT NULL DEFAULT (UUID()),
    `Email` VARCHAR(100) NOT NULL,
    `Question` TEXT NOT NULL,
    `Answer` TEXT,
    `AnswerBy` VARCHAR(36) NOT NULL,
    PRIMARY KEY (`TicketId`)
);

CREATE TABLE `TicketDocuments` (
    `TicketId` VARCHAR(36) NOT NULL,
    `Path` VARCHAR(100) NOT NULL,
    `Type` ENUM('IMAGE', 'VIDEO') NOT NULL
);

CREATE TABLE `Inbounds` (
    `InboundId` VARCHAR(36) NOT NULL DEFAULT (UUID()),
    `Status` ENUM('PENDING', 'SRIKANDI', 'ACCEPTED', 'CANCELLED', 'DONE') NOT NULL,
    `Name` VARCHAR(128) NOT NULL,
    `Birthdate` DATE NOT NULL,
    `BirthLocation` VARCHAR(128) NOT NULL,
    `Address` TEXT NOT NULL,
    `PasporNum` VARCHAR(9) NOT NULL,
    `PasporExp` DATE NOT NULL,
    `LetterType` ENUM('WARRANTY', 'PERMIT') NOT NULL,
    `InboundProgramId` VARCHAR(36) NOT NULL,
    `Duration` INT,
    `DurationType` ENUM('YEAR', 'MONTH', 'DAY'),
    `DecideBy` VARCHAR(36),
    `DecidedAt` TIMESTAMP,
    `InboundLetterPath` VARCHAR(100),
    PRIMARY KEY (`InboundId`)
);

CREATE TABLE `PDLNParticapants` (
    `UserId` VARCHAR(36) NOT NULL,
    `PDLNId` VARCHAR(36) NOT NULL
);

CREATE TABLE `Departments` (
    `DepartmentId` VARCHAR(36) NOT NULL DEFAULT (UUID()),
    `Name` VARCHAR(64) NOT NULL,
    PRIMARY KEY (`DepartmentId`)
);

CREATE TABLE `StudyPrograms` (
    `StudyProgramId` VARCHAR(36) NOT NULL DEFAULT (UUID()),
    `DepartmentId` VARCHAR(36) NOT NULL,
    `Name` VARCHAR(64) NOT NULL,
    PRIMARY KEY (`StudyProgramId`)
);

CREATE TABLE `Programs` (
    `ProgramId` VARCHAR(36) NOT NULL DEFAULT (UUID()),
    `StudyProgramId` VARCHAR(36) NOT NULL,
    `Name` VARCHAR(64) NOT NULL,
    PRIMARY KEY (`ProgramId`)
);

CREATE TABLE `DocumentTemplates` (
    `DocTypeId` VARCHAR(36) NOT NULL,
    `Path` VARCHAR(100) NOT NULL,
    `CreatedAt` TIMESTAMP NOT NULL,
    `UpdatedAt` TIMESTAMP NOT NULL
);

CREATE TABLE `FAQs` (
    `FAQId` VARCHAR(36) NOT NULL DEFAULT (UUID()),
    `Question` TEXT NOT NULL,
    `Answer` TEXT NOT NULL,
    `UpdateBy` VARCHAR(36) NOT NULL,
    PRIMARY KEY (`FAQId`)
);

CREATE TABLE `InboundPrograms` (
    `InboundProgramId` VARCHAR(36) NOT NULL DEFAULT (UUID()),
    `Name` TEXT NOT NULL,
    `StudyProgram` TEXT NOT NULL,
    `StartDate` DATE NOT NULL,
    `EndDate` DATE NOT NULL,
    PRIMARY KEY (`InboundProgramId`)
);

ALTER TABLE `PDLNDocuments` ADD FOREIGN KEY (`DocTypeId`) REFERENCES `DocumentTypes`(`DocTypeId`);
ALTER TABLE `PDLNDocuments` ADD FOREIGN KEY (`PDLNId`) REFERENCES `PDLNs`(`PDLNId`);
ALTER TABLE `PDLNs` ADD FOREIGN KEY (`UserId`) REFERENCES `Users`(`UserId`);
ALTER TABLE `Reports` ADD FOREIGN KEY (`PDLNId`) REFERENCES `PDLNs`(`PDLNId`);
ALTER TABLE `Users` ADD FOREIGN KEY (`DepartmentId`) REFERENCES `Departments`(`DepartmentId`);
ALTER TABLE `Users` ADD FOREIGN KEY (`StudyProgramId`) REFERENCES `StudyPrograms`(`StudyProgramId`);
ALTER TABLE `Users` ADD FOREIGN KEY (`ProgramId`) REFERENCES `Programs`(`ProgramId`);
ALTER TABLE `Outbounds` ADD FOREIGN KEY (`UserId`) REFERENCES `Users`(`UserId`);
ALTER TABLE `Outbounds` ADD FOREIGN KEY (`DecideBy`) REFERENCES `Users`(`UserId`);
ALTER TABLE `Tickets` ADD FOREIGN KEY (`AnswerBy`) REFERENCES `Users`(`UserId`);
ALTER TABLE `TicketDocuments` ADD FOREIGN KEY (`TicketId`) REFERENCES `Tickets`(`TicketId`);
ALTER TABLE `Inbounds` ADD FOREIGN KEY (`InboundProgramId`) REFERENCES `InboundPrograms`(`InboundProgramId`);
ALTER TABLE `Inbounds` ADD FOREIGN KEY (`DecideBy`) REFERENCES `Users`(`UserId`);
ALTER TABLE `PDLNParticapants` ADD FOREIGN KEY (`PDLNId`) REFERENCES `PDLNs`(`PDLNId`);
ALTER TABLE `PDLNParticapants` ADD FOREIGN KEY (`UserId`) REFERENCES `Users`(`UserId`);
ALTER TABLE `StudyPrograms` ADD FOREIGN KEY (`DepartmentId`) REFERENCES `Departments`(`DepartmentId`);
ALTER TABLE `Programs` ADD FOREIGN KEY (`StudyProgramId`) REFERENCES `StudyPrograms`(`StudyProgramId`);
ALTER TABLE `DocumentTemplates` ADD FOREIGN KEY (`DocTypeId`) REFERENCES `DocumentTypes`(`DocTypeId`);
ALTER TABLE `FAQs` ADD FOREIGN KEY (`UpdateBy`) REFERENCES `Users`(`UserId`);
