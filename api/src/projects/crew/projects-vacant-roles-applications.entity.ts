import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Projectsvacantroles } from "./projects-vacant-roles.entity";
import { Users } from "../../auth/users/users.entity";

@Index(
  "projectsVacantRolesApplications_projectsVacantRolesid_fk",
  ["projectsVacantRolesId"],
  {},
)
@Index(
  "projectsVacantRolesApplications_users_users_userid_fk",
  ["usersUserid"],
  {},
)
@Entity("projectsvacantrolesapplications", { schema: "adamrms" })
export class Projectsvacantrolesapplications {
  @PrimaryGeneratedColumn({
    type: "int",
    name: "projectsVacantRolesApplications_id",
  })
  projectsVacantRolesApplicationsId: number;

  @Column("int", { name: "projectsVacantRoles_id" })
  projectsVacantRolesId: number;

  @Column("int", { name: "users_userid" })
  usersUserid: number;

  @Column("text", {
    name: "projectsVacantRolesApplications_files",
    nullable: true,
  })
  projectsVacantRolesApplicationsFiles: string | null;

  @Column("varchar", {
    name: "projectsVacantRolesApplications_phone",
    nullable: true,
    length: 255,
  })
  projectsVacantRolesApplicationsPhone: string | null;

  @Column("text", {
    name: "projectsVacantRolesApplications_applicantComment",
    nullable: true,
  })
  projectsVacantRolesApplicationsApplicantComment: string | null;

  @Column("tinyint", {
    name: "projectsVacantRolesApplications_deleted",
    width: 1,
    default: () => "'0'",
  })
  projectsVacantRolesApplicationsDeleted: boolean;

  @Column("tinyint", {
    name: "projectsVacantRolesApplications_withdrawn",
    width: 1,
    default: () => "'0'",
  })
  projectsVacantRolesApplicationsWithdrawn: boolean;

  @Column("timestamp", {
    name: "projectsVacantRolesApplications_submitted",
    default: () => "CURRENT_TIMESTAMP",
  })
  projectsVacantRolesApplicationsSubmitted: Date;

  @Column("json", {
    name: "projectsVacantRolesApplications_questionAnswers",
    nullable: true,
  })
  projectsVacantRolesApplicationsQuestionAnswers: object | null;

  @Column("tinyint", {
    name: "projectsVacantRolesApplications_status",
    comment: "1 = Success\r\n2 = Rejected",
    width: 1,
    default: () => "'0'",
  })
  projectsVacantRolesApplicationsStatus: boolean;

  @ManyToOne(
    () => Projectsvacantroles,
    (projectsvacantroles) =>
      projectsvacantroles.projectsvacantrolesapplications,
    { onDelete: "CASCADE", onUpdate: "CASCADE" },
  )
  @JoinColumn([
    {
      name: "projectsVacantRoles_id",
      referencedColumnName: "projectsVacantRolesId",
    },
  ])
  projectsVacantRoles: Projectsvacantroles;

  @ManyToOne(() => Users, (users) => users.projectsvacantrolesapplications, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;
}
