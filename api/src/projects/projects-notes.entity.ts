import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Users } from "../auth/users/users.entity";
import { Projects } from "./projects.entity";

@Index("projectsNotes_projects_projects_id_fk", ["projectsId"], {})
@Index("projectsNotes_users_users_userid_fk", ["projectsNotesUserid"], {})
@Entity("projectsnotes", { schema: "adamrms" })
export class Projectsnotes {
  @PrimaryGeneratedColumn({ type: "int", name: "projectsNotes_id" })
  projectsNotesId: number;

  @Column("varchar", { name: "projectsNotes_title", length: 200 })
  projectsNotesTitle: string;

  @Column("text", { name: "projectsNotes_text", nullable: true })
  projectsNotesText: string | null;

  @Column("int", { name: "projectsNotes_userid" })
  projectsNotesUserid: number;

  @Column("int", { name: "projects_id" })
  projectsId: number;

  @Column("tinyint", {
    name: "projectsNotes_deleted",
    width: 1,
    default: () => "'0'",
  })
  projectsNotesDeleted: boolean;

  @ManyToOne(() => Projects, (projects) => projects.projectsnotes, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "projects_id", referencedColumnName: "projectsId" }])
  projects: Projects;

  @ManyToOne(() => Users, (users) => users.projectsnotes, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([
    { name: "projectsNotes_userid", referencedColumnName: "usersUserid" },
  ])
  projectsNotesUser: Users;
}
