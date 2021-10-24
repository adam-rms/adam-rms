import { Column, Entity, OneToMany, PrimaryGeneratedColumn } from "typeorm";
import { Assettypes } from "../assets/asset-types.entity";
import { Assets } from "../assets/assets.entity";
import { Assetsassignmentsstatus } from "../assets/assignments/assets-assignments-status.entity";
import { Assetcategories } from "../assets/categories/asset-categories.entity";
import { Assetgroups } from "../assets/groups/asset-groups.entity";
import { Manufacturers } from "../assets/manufacturers.entity";
import { Clients } from "../clients/clients.entity";
import { Cmspages } from "../cms/cms-pages.entity";
import { S3files } from "../files/s3-files.entity";
import { Instancepositions } from "../instances/permissions/instance-positions.entity";
import { Locations } from "../locations/locations.entity";
import { Maintenancejobsstatuses } from "../maintenance/maintenance-jobs-statuses.entity";
import { Projectstypes } from "../projects/projects-types.entity";
import { Projects } from "../projects/projects.entity";
import { Modules } from "../training/modules.entity";
import { Signupcodes } from "./signup-codes.entity";

@Entity("instances", { schema: "adamrms" })
export class Instances {
  @PrimaryGeneratedColumn({ type: "int", name: "instances_id" })
  instancesId: number;

  @Column("varchar", { name: "instances_name", length: 200 })
  instancesName: string;

  @Column("tinyint", {
    name: "instances_deleted",
    nullable: true,
    width: 1,
    default: () => "'0'",
  })
  instancesDeleted: boolean | null;

  @Column("varchar", { name: "instances_plan", nullable: true, length: 500 })
  instancesPlan: string | null;

  @Column("varchar", {
    name: "instances_address",
    nullable: true,
    length: 1000,
  })
  instancesAddress: string | null;

  @Column("varchar", { name: "instances_phone", nullable: true, length: 200 })
  instancesPhone: string | null;

  @Column("varchar", { name: "instances_email", nullable: true, length: 200 })
  instancesEmail: string | null;

  @Column("varchar", { name: "instances_website", nullable: true, length: 200 })
  instancesWebsite: string | null;

  @Column("text", { name: "instances_weekStartDates", nullable: true })
  instancesWeekStartDates: string | null;

  @Column("int", { name: "instances_logo", nullable: true })
  instancesLogo: number | null;

  @Column("int", {
    name: "instances_emailHeader",
    nullable: true,
    comment: "A 1200x600 image to be the header on their emails",
  })
  instancesEmailHeader: number | null;

  @Column("text", { name: "instances_termsAndPayment", nullable: true })
  instancesTermsAndPayment: string | null;

  @Column("text", { name: "instances_quoteTerms", nullable: true })
  instancesQuoteTerms: string | null;

  @Column("bigint", {
    name: "instances_storageLimit",
    comment: "In bytes - 500mb is default",
    default: () => "'524288000'",
  })
  instancesStorageLimit: string;

  @Column("double", {
    name: "instances_config_linkedDefaultDiscount",
    nullable: true,
    precision: 22,
    scale: 2,
    default: () => "'100'",
  })
  instancesConfigLinkedDefaultDiscount: number | null;

  @Column("varchar", {
    name: "instances_config_currency",
    length: 200,
    default: () => "'GBP'",
  })
  instancesConfigCurrency: string;

  @Column("text", { name: "instances_cableColours", nullable: true })
  instancesCableColours: string | null;

  @Column("text", { name: "instances_publicConfig", nullable: true })
  instancesPublicConfig: string | null;

  @OneToMany(
    () => Assetcategories,
    (assetcategories) => assetcategories.instances,
  )
  assetcategories: Assetcategories[];

  @OneToMany(() => Assetgroups, (assetgroups) => assetgroups.instances)
  assetgroups: Assetgroups[];

  @OneToMany(() => Assets, (assets) => assets.instances)
  assets: Assets[];

  @OneToMany(
    () => Assetsassignmentsstatus,
    (assetsassignmentsstatus) => assetsassignmentsstatus.instances,
  )
  assetsassignmentsstatuses: Assetsassignmentsstatus[];

  @OneToMany(() => Assettypes, (assettypes) => assettypes.instances)
  assettypes: Assettypes[];

  @OneToMany(() => Clients, (clients) => clients.instances)
  clients: Clients[];

  @OneToMany(() => Cmspages, (cmspages) => cmspages.instances)
  cmspages: Cmspages[];

  @OneToMany(
    () => Instancepositions,
    (instancepositions) => instancepositions.instances,
  )
  instancepositions: Instancepositions[];

  @OneToMany(() => Locations, (locations) => locations.instances)
  locations: Locations[];

  @OneToMany(
    () => Maintenancejobsstatuses,
    (maintenancejobsstatuses) => maintenancejobsstatuses.instances,
  )
  maintenancejobsstatuses: Maintenancejobsstatuses[];

  @OneToMany(() => Manufacturers, (manufacturers) => manufacturers.instances)
  manufacturers: Manufacturers[];

  @OneToMany(() => Modules, (modules) => modules.instances)
  modules: Modules[];

  @OneToMany(() => Projects, (projects) => projects.instances)
  projects: Projects[];

  @OneToMany(() => Projectstypes, (projectstypes) => projectstypes.instances)
  projectstypes: Projectstypes[];

  @OneToMany(() => S3files, (s3files) => s3files.instances)
  s3files: S3files[];

  @OneToMany(() => Signupcodes, (signupcodes) => signupcodes.instances)
  signupcodes: Signupcodes[];
}
