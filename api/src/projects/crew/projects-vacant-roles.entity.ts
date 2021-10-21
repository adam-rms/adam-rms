import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Projects } from "../projects.entity";
import { Projectsvacantrolesapplications } from "./projects-vacant-roles-applications.entity";

@Index("projectsVacantRoles_projects_projects_id_fk", ["projectsId"], {})
@Entity("projectsvacantroles", { schema: "adamrms" })
export class Projectsvacantroles {
  @PrimaryGeneratedColumn({ type: "int", name: "projectsVacantRoles_id" })
  projectsVacantRolesId: number;

  @Column("int", { name: "projects_id" })
  projectsId: number;

  @Column("varchar", { name: "projectsVacantRoles_name", length: 500 })
  projectsVacantRolesName: string;

  @Column("text", { name: "projectsVacantRoles_description", nullable: true })
  projectsVacantRolesDescription: string | null;

  @Column("text", {
    name: "projectsVacantRoles_personSpecification",
    nullable: true,
  })
  projectsVacantRolesPersonSpecification: string | null;

  @Column("tinyint", {
    name: "projectsVacantRoles_deleted",
    width: 1,
    default: () => "'0'",
  })
  projectsVacantRolesDeleted: boolean;

  @Column("tinyint", {
    name: "projectsVacantRoles_open",
    width: 1,
    default: () => "'0'",
  })
  projectsVacantRolesOpen: boolean;

  @Column("tinyint", {
    name: "projectsVacantRoles_showPublic",
    width: 1,
    default: () => "'0'",
  })
  projectsVacantRolesShowPublic: boolean;

  @Column("timestamp", {
    name: "projectsVacantRoles_added",
    default: () => "CURRENT_TIMESTAMP",
  })
  projectsVacantRolesAdded: Date;

  @Column("timestamp", { name: "projectsVacantRoles_deadline", nullable: true })
  projectsVacantRolesDeadline: Date | null;

  @Column("tinyint", {
    name: "projectsVacantRoles_firstComeFirstServed",
    width: 1,
    default: () => "'0'",
  })
  projectsVacantRolesFirstComeFirstServed: boolean;

  @Column("tinyint", {
    name: "projectsVacantRoles_fileUploads",
    width: 1,
    default: () => "'1'",
  })
  projectsVacantRolesFileUploads: boolean;

  @Column("int", { name: "projectsVacantRoles_slots", default: () => "'1'" })
  projectsVacantRolesSlots: number;

  @Column("int", {
    name: "projectsVacantRoles_slotsFilled",
    default: () => "'0'",
  })
  projectsVacantRolesSlotsFilled: number;

  @Column("json", { name: "projectsVacantRoles_questions", nullable: true })
  projectsVacantRolesQuestions: object | null;

  @Column("tinyint", {
    name: "projectsVacantRoles_collectPhone",
    width: 1,
    default: () => "'0'",
  })
  projectsVacantRolesCollectPhone: boolean;

  @Column("tinyint", {
    name: "projectsVacantRoles_privateToPM",
    width: 1,
    default: () => "'1'",
  })
  projectsVacantRolesPrivateToPm: boolean;

  @ManyToOne(() => Projects, (projects) => projects.projectsvacantroles, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "projects_id", referencedColumnName: "projectsId" }])
  projects: Projects;

  @OneToMany(
    () => Projectsvacantrolesapplications,
    (projectsvacantrolesapplications) =>
      projectsvacantrolesapplications.projectsVacantRoles,
  )
  projectsvacantrolesapplications: Projectsvacantrolesapplications[];
}
