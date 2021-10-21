import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Users } from "../../auth/users/users.entity";
import { Projects } from "../projects.entity";

@Index("crewAssignments_projects_projects_id_fk", ["projectsId"], {})
@Index("crewAssignments_users_users_userid_fk", ["usersUserid"], {})
@Entity("crewassignments", { schema: "adamrms" })
export class Crewassignments {
  @PrimaryGeneratedColumn({ type: "int", name: "crewAssignments_id" })
  crewAssignmentsId: number;

  @Column("int", { name: "users_userid", nullable: true })
  usersUserid: number | null;

  @Column("int", { name: "projects_id" })
  projectsId: number;

  @Column("varchar", {
    name: "crewAssignments_personName",
    nullable: true,
    length: 500,
  })
  crewAssignmentsPersonName: string | null;

  @Column("varchar", { name: "crewAssignments_role", length: 500 })
  crewAssignmentsRole: string;

  @Column("varchar", {
    name: "crewAssignments_comment",
    nullable: true,
    length: 500,
  })
  crewAssignmentsComment: string | null;

  @Column("tinyint", {
    name: "crewAssignments_deleted",
    nullable: true,
    width: 1,
    default: () => "'0'",
  })
  crewAssignmentsDeleted: boolean | null;

  @Column("int", {
    name: "crewAssignments_rank",
    nullable: true,
    default: () => "'99'",
  })
  crewAssignmentsRank: number | null;

  @ManyToOne(() => Projects, (projects) => projects.crewassignments, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "projects_id", referencedColumnName: "projectsId" }])
  projects: Projects;

  @ManyToOne(() => Users, (users) => users.crewassignments, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;
}
