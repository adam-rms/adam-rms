import {
  Column,
  Entity,
  Index,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Assetsbarcodesscans } from "../../assets/barcodes/assets-barcodes-scans.entity";
import { Assetsbarcodes } from "../../assets/barcodes/assets-barcodes.entity";
import { Assetgroups } from "../../assets/groups/asset-groups.entity";
import { Cmspagesdrafts } from "../../cms/cms-pages-drafts.entity";
import { Cmspagesviews } from "../../cms/cms-pages-views.entity";
import { S3files } from "../../files/s3-files.entity";
import { Userinstances } from "../../instances/user-instances.entity";
import { Locationsbarcodes } from "../../locations/barcodes/locations-barcodes.entity";
import { Maintenancejobs } from "../../maintenance/maintenance-jobs.entity";
import { Crewassignments } from "../../projects/crew/crew-assignments.entity";
import { Projectsvacantrolesapplications } from "../../projects/crew/projects-vacant-roles-applications.entity";
import { Projectsnotes } from "../../projects/projects-notes.entity";
import { Projects } from "../../projects/projects.entity";
import { Modules } from "../../training/modules.entity";
import { Usermodulescertifications } from "../../training/user-modules-certifications.entity";
import { Usermodules } from "../../training/user-modules.entity";
import { Auditlog } from "../audit-log.entity";
import { Emailsent } from "../email/email-sent.entity";
import { Userpositions } from "../user-positions.entity";
import { Authtokens } from "./auth-tokens.entity";
import { Emailverificationcodes } from "./email-verification-codes.entity";
import { Passwordresetcodes } from "./password-reset-codes.entity";

@Index("users_users_email_uindex", ["usersEmail"], { unique: true })
@Index("users_users_username_uindex", ["usersUsername"], { unique: true })
@Index("username_2", ["usersUserid"], {})
@Entity("users", { schema: "adamrms" })
export class Users {
  @Column("varchar", {
    name: "users_username",
    nullable: true,
    unique: true,
    length: 200,
  })
  usersUsername: string | null;

  @Column("varchar", { name: "users_name1", nullable: true, length: 100 })
  usersName1: string | null;

  @Column("varchar", { name: "users_name2", nullable: true, length: 100 })
  usersName2: string | null;

  @PrimaryGeneratedColumn({ type: "int", name: "users_userid" })
  usersUserid: number;

  @Column("varchar", { name: "users_salty1", nullable: true, length: 30 })
  usersSalty1: string | null;

  @Column("varchar", { name: "users_password", nullable: true, length: 150 })
  usersPassword: string | null;

  @Column("varchar", { name: "users_salty2", nullable: true, length: 50 })
  usersSalty2: string | null;

  @Column("varchar", { name: "users_hash", length: 255 })
  usersHash: string;

  @Column("varchar", {
    name: "users_email",
    nullable: true,
    unique: true,
    length: 257,
  })
  usersEmail: string | null;

  @Column("timestamp", {
    name: "users_created",
    nullable: true,
    comment: "When user signed up",
    default: () => "CURRENT_TIMESTAMP",
  })
  usersCreated: Date | null;

  @Column("text", {
    name: "users_notes",
    nullable: true,
    comment: "Internal Notes - Not visible to user",
  })
  usersNotes: string | null;

  @Column("int", { name: "users_thumbnail", nullable: true })
  usersThumbnail: number | null;

  @Column("tinyint", {
    name: "users_changepass",
    width: 1,
    default: () => "'0'",
  })
  usersChangepass: boolean;

  @Column("int", { name: "users_selectedProjectID", nullable: true })
  usersSelectedProjectId: number | null;

  @Column("int", {
    name: "users_selectedInstanceIDLast",
    nullable: true,
    comment:
      "What is the instance ID they most recently selected? This will be the one we use next time they login",
  })
  usersSelectedInstanceIdLast: number | null;

  @Column("tinyint", {
    name: "users_suspended",
    width: 1,
    default: () => "'0'",
  })
  usersSuspended: boolean;

  @Column("tinyint", {
    name: "users_deleted",
    nullable: true,
    width: 1,
    default: () => "'0'",
  })
  usersDeleted: boolean | null;

  @Column("tinyint", {
    name: "users_emailVerified",
    width: 1,
    default: () => "'0'",
  })
  usersEmailVerified: boolean;

  @Column("varchar", {
    name: "users_social_facebook",
    nullable: true,
    length: 100,
  })
  usersSocialFacebook: string | null;

  @Column("varchar", {
    name: "users_social_twitter",
    nullable: true,
    length: 100,
  })
  usersSocialTwitter: string | null;

  @Column("varchar", {
    name: "users_social_instagram",
    nullable: true,
    length: 100,
  })
  usersSocialInstagram: string | null;

  @Column("varchar", {
    name: "users_social_linkedin",
    nullable: true,
    length: 100,
  })
  usersSocialLinkedin: string | null;

  @Column("varchar", {
    name: "users_social_snapchat",
    nullable: true,
    length: 100,
  })
  usersSocialSnapchat: string | null;

  @Column("varchar", {
    name: "users_calendarHash",
    nullable: true,
    length: 200,
  })
  usersCalendarHash: string | null;

  @Column("varchar", { name: "users_widgets", nullable: true, length: 500 })
  usersWidgets: string | null;

  @Column("text", { name: "users_notificationSettings", nullable: true })
  usersNotificationSettings: string | null;

  @Column("varchar", {
    name: "users_assetGroupsWatching",
    nullable: true,
    length: 200,
  })
  usersAssetGroupsWatching: string | null;

  @Column("varchar", {
    name: "users_oauth_googleid",
    nullable: true,
    length: 255,
  })
  usersOauthGoogleid: string | null;

  @Column("tinyint", {
    name: "users_dark_mode",
    width: 1,
    default: () => "'0'",
  })
  usersDarkMode: boolean;

  @OneToMany(() => Assetgroups, (assetgroups) => assetgroups.usersUser)
  assetgroups: Assetgroups[];

  @OneToMany(() => Assetsbarcodes, (assetsbarcodes) => assetsbarcodes.usersUser)
  assetsbarcodes: Assetsbarcodes[];

  @OneToMany(
    () => Assetsbarcodesscans,
    (assetsbarcodesscans) => assetsbarcodesscans.usersUser,
  )
  assetsbarcodesscans: Assetsbarcodesscans[];

  @OneToMany(() => Auditlog, (auditlog) => auditlog.usersUser)
  auditlogs: Auditlog[];

  @OneToMany(() => Auditlog, (auditlog) => auditlog.auditLogActionUser)
  auditlogs2: Auditlog[];

  @OneToMany(() => Authtokens, (authtokens) => authtokens.usersUser)
  authtokens: Authtokens[];

  @OneToMany(() => Authtokens, (authtokens) => authtokens.authTokensAdmin)
  authtokens2: Authtokens[];

  @OneToMany(() => Cmspagesdrafts, (cmspagesdrafts) => cmspagesdrafts.usersUser)
  cmspagesdrafts: Cmspagesdrafts[];

  @OneToMany(() => Cmspagesviews, (cmspagesviews) => cmspagesviews.usersUser)
  cmspagesviews: Cmspagesviews[];

  @OneToMany(
    () => Crewassignments,
    (crewassignments) => crewassignments.usersUser,
  )
  crewassignments: Crewassignments[];

  @OneToMany(() => Emailsent, (emailsent) => emailsent.usersUser)
  emailsents: Emailsent[];

  @OneToMany(
    () => Emailverificationcodes,
    (emailverificationcodes) => emailverificationcodes.usersUser,
  )
  emailverificationcodes: Emailverificationcodes[];

  @OneToMany(
    () => Locationsbarcodes,
    (locationsbarcodes) => locationsbarcodes.usersUser,
  )
  locationsbarcodes: Locationsbarcodes[];

  @OneToMany(
    () => Maintenancejobs,
    (maintenancejobs) => maintenancejobs.maintenanceJobsUserCreator2,
  )
  maintenancejobs: Maintenancejobs[];

  @OneToMany(() => Modules, (modules) => modules.usersUser)
  modules: Modules[];

  @OneToMany(
    () => Passwordresetcodes,
    (passwordresetcodes) => passwordresetcodes.usersUser,
  )
  passwordresetcodes: Passwordresetcodes[];

  @OneToMany(() => Projects, (projects) => projects.projectsManager2)
  projects: Projects[];

  @OneToMany(
    () => Projectsnotes,
    (projectsnotes) => projectsnotes.projectsNotesUser,
  )
  projectsnotes: Projectsnotes[];

  @OneToMany(
    () => Projectsvacantrolesapplications,
    (projectsvacantrolesapplications) =>
      projectsvacantrolesapplications.usersUser,
  )
  projectsvacantrolesapplications: Projectsvacantrolesapplications[];

  @OneToMany(() => S3files, (s3files) => s3files.usersUser)
  s3files: S3files[];

  @OneToMany(() => Userinstances, (userinstances) => userinstances.usersUser)
  userinstances: Userinstances[];

  @OneToMany(() => Usermodules, (usermodules) => usermodules.usersUser)
  usermodules: Usermodules[];

  @OneToMany(
    () => Usermodulescertifications,
    (usermodulescertifications) => usermodulescertifications.usersUser,
  )
  usermodulescertifications: Usermodulescertifications[];

  @OneToMany(
    () => Usermodulescertifications,
    (usermodulescertifications) =>
      usermodulescertifications.userModulesCertificationsApprovedBy2,
  )
  usermodulescertifications2: Usermodulescertifications[];

  @OneToMany(() => Userpositions, (userpositions) => userpositions.usersUser)
  userpositions: Userpositions[];
}
