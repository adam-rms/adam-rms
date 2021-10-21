import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Users } from "../auth/users/users.entity";
import { Modules } from "./modules.entity";

@Index("userModulesCertifications_users_users_userid_fk", ["usersUserid"], {})
@Index(
  "userModulesCertifications_users_users_userid_fk_2",
  ["userModulesCertificationsApprovedBy"],
  {},
)
@Index("userModulesCertifications_modules_modules_id_fk", ["modulesId"], {})
@Entity("usermodulescertifications", { schema: "adamrms" })
export class Usermodulescertifications {
  @PrimaryGeneratedColumn({ type: "int", name: "userModulesCertifications_id" })
  userModulesCertificationsId: number;

  @Column("int", { name: "modules_id" })
  modulesId: number;

  @Column("int", { name: "users_userid" })
  usersUserid: number;

  @Column("tinyint", {
    name: "userModulesCertifications_revoked",
    width: 1,
    default: () => "'0'",
  })
  userModulesCertificationsRevoked: boolean;

  @Column("int", { name: "userModulesCertifications_approvedBy" })
  userModulesCertificationsApprovedBy: number;

  @Column("varchar", {
    name: "userModulesCertifications_approvedComment",
    nullable: true,
    length: 2000,
  })
  userModulesCertificationsApprovedComment: string | null;

  @Column("timestamp", { name: "userModulesCertifications_timestamp" })
  userModulesCertificationsTimestamp: Date;

  @ManyToOne(() => Modules, (modules) => modules.usermodulescertifications, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "modules_id", referencedColumnName: "modulesId" }])
  modules: Modules;

  @ManyToOne(() => Users, (users) => users.usermodulescertifications, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;

  @ManyToOne(() => Users, (users) => users.usermodulescertifications2, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([
    {
      name: "userModulesCertifications_approvedBy",
      referencedColumnName: "usersUserid",
    },
  ])
  userModulesCertificationsApprovedBy2: Users;
}
