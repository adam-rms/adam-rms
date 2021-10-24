import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Assetsassignments } from "../assets/assignments/assets-assignments.entity";
import { Users } from "../auth/users/users.entity";
import { Clients } from "../clients/clients.entity";
import { Instances } from "../instances/instances.entity";
import { Locations } from "../locations/locations.entity";
import { Crewassignments } from "./crew/crew-assignments.entity";
import { Projectsvacantroles } from "./crew/projects-vacant-roles.entity";
import { Payments } from "./finance/payments.entity";
import { Projectsfinancecache } from "./finance/projects-finance-cache.entity";
import { Projectsnotes } from "./projects-notes.entity";

@Index("projects_clients_clients_id_fk", ["clientsId"], {})
@Index("projects_instances_instances_id_fk", ["instancesId"], {})
@Index("projects_users_users_userid_fk", ["projectsManager"], {})
@Index("projects_locations_locations_id_fk", ["locationsId"], {})
@Index("projects_projectsTypes_projectsTypes_id_fk", ["projectsTypesId"], {})
@Index("projects_parent_project_id", ["projectsParentProjectId"], {})
@Entity("projects", { schema: "adamrms" })
export class Projects {
  @PrimaryGeneratedColumn({ type: "int", name: "projects_id" })
  projectsId: number;

  @Column("varchar", { name: "projects_name", length: 500 })
  projectsName: string;

  @Column("int", { name: "instances_id" })
  instancesId: number;

  @Column("int", { name: "projects_manager" })
  projectsManager: number;

  @Column("text", { name: "projects_description", nullable: true })
  projectsDescription: string | null;

  @Column("timestamp", {
    name: "projects_created",
    default: () => "CURRENT_TIMESTAMP",
  })
  projectsCreated: Date;

  @Column("int", { name: "clients_id", nullable: true })
  clientsId: number | null;

  @Column("tinyint", {
    name: "projects_deleted",
    width: 1,
    default: () => "'0'",
  })
  projectsDeleted: boolean;

  @Column("tinyint", {
    name: "projects_archived",
    width: 1,
    default: () => "'0'",
  })
  projectsArchived: boolean;

  @Column("timestamp", { name: "projects_dates_use_start", nullable: true })
  projectsDatesUseStart: Date | null;

  @Column("timestamp", { name: "projects_dates_use_end", nullable: true })
  projectsDatesUseEnd: Date | null;

  @Column("timestamp", { name: "projects_dates_deliver_start", nullable: true })
  projectsDatesDeliverStart: Date | null;

  @Column("timestamp", { name: "projects_dates_deliver_end", nullable: true })
  projectsDatesDeliverEnd: Date | null;

  @Column("tinyint", {
    name: "projects_status",
    comment: "Provisional",
    default: () => "'0'",
  })
  projectsStatus: number;

  @Column("int", { name: "locations_id", nullable: true })
  locationsId: number | null;

  @Column("text", { name: "projects_invoiceNotes", nullable: true })
  projectsInvoiceNotes: string | null;

  @Column("double", {
    name: "projects_defaultDiscount",
    precision: 22,
    scale: 2,
    default: 0,
  })
  projectsDefaultDiscount: number;

  @Column("int", { name: "projectsTypes_id" })
  projectsTypesId: number;

  @Column("int", { name: "projects_parent_project_id", nullable: true })
  projectsParentProjectId: number | null;

  @OneToMany(
    () => Assetsassignments,
    (assetsassignments) => assetsassignments.projects,
  )
  assetsassignments: Assetsassignments[];

  @OneToMany(
    () => Crewassignments,
    (crewassignments) => crewassignments.projects,
  )
  crewassignments: Crewassignments[];

  @OneToMany(() => Payments, (payments) => payments.projects)
  payments: Payments[];

  @ManyToOne(() => Clients, (clients) => clients.projects, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "clients_id", referencedColumnName: "clientsId" }])
  clients: Clients;

  @ManyToOne(() => Projects, (projects) => projects.projects, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([
    { name: "projects_parent_project_id", referencedColumnName: "projectsId" },
  ])
  projectsParentProject: Projects;

  @OneToMany(() => Projects, (projects) => projects.projectsParentProject)
  projects: Projects[];

  @ManyToOne(() => Instances, (instances) => instances.projects, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;

  @ManyToOne(() => Locations, (locations) => locations.projects, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "locations_id", referencedColumnName: "locationsId" }])
  locations: Locations;

  @ManyToOne(() => Users, (users) => users.projects, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([
    { name: "projects_manager", referencedColumnName: "usersUserid" },
  ])
  projectsManager2: Users;

  @OneToMany(
    () => Projectsfinancecache,
    (projectsfinancecache) => projectsfinancecache.projects,
  )
  projectsfinancecaches: Projectsfinancecache[];

  @OneToMany(() => Projectsnotes, (projectsnotes) => projectsnotes.projects)
  projectsnotes: Projectsnotes[];

  @OneToMany(
    () => Projectsvacantroles,
    (projectsvacantroles) => projectsvacantroles.projects,
  )
  projectsvacantroles: Projectsvacantroles[];
}
