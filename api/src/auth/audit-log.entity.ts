import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Users } from "./users/users.entity";

@Index("auditLog_users_users_userid_fk", ["usersUserid"], {})
@Index("auditLog_users_users_userid_fk_2", ["auditLogActionUserid"], {})
@Entity("auditlog", { schema: "adamrms" })
export class Auditlog {
  @PrimaryGeneratedColumn({ type: "int", name: "auditLog_id" })
  auditLogId: number;

  @Column("varchar", {
    name: "auditLog_actionType",
    nullable: true,
    length: 500,
  })
  auditLogActionType: string | null;

  @Column("varchar", {
    name: "auditLog_actionTable",
    nullable: true,
    length: 500,
  })
  auditLogActionTable: string | null;

  @Column("longtext", { name: "auditLog_actionData", nullable: true })
  auditLogActionData: string | null;

  @Column("timestamp", {
    name: "auditLog_timestamp",
    default: () => "CURRENT_TIMESTAMP",
  })
  auditLogTimestamp: Date;

  @Column("int", { name: "users_userid", nullable: true })
  usersUserid: number | null;

  @Column("int", { name: "auditLog_actionUserid", nullable: true })
  auditLogActionUserid: number | null;

  @Column("int", { name: "projects_id", nullable: true })
  projectsId: number | null;

  @Column("tinyint", {
    name: "auditLog_deleted",
    width: 1,
    default: () => "'0'",
  })
  auditLogDeleted: boolean;

  @Column("int", { name: "auditLog_targetID", nullable: true })
  auditLogTargetId: number | null;

  @ManyToOne(() => Users, (users) => users.auditlogs, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;

  @ManyToOne(() => Users, (users) => users.auditlogs2, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([
    { name: "auditLog_actionUserid", referencedColumnName: "usersUserid" },
  ])
  auditLogActionUser: Users;
}
