import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Users } from "./users.entity";

@Index("token", ["authTokensToken"], { unique: true })
@Index("authTokens_users_users_userid_fk", ["usersUserid"], {})
@Index("authTokens_users_users_userid_fk_2", ["authTokensAdminId"], {})
@Entity("authtokens", { schema: "adamrms" })
export class Authtokens {
  @PrimaryGeneratedColumn({ type: "int", name: "authTokens_id" })
  authTokensId: number;

  @Column("varchar", { name: "authTokens_token", unique: true, length: 500 })
  authTokensToken: string;

  @Column("timestamp", {
    name: "authTokens_created",
    default: () => "CURRENT_TIMESTAMP",
  })
  authTokensCreated: Date;

  @Column("varchar", {
    name: "authTokens_ipAddress",
    nullable: true,
    length: 500,
  })
  authTokensIpAddress: string | null;

  @Column("int", { name: "users_userid" })
  usersUserid: number;

  @Column("tinyint", {
    name: "authTokens_valid",
    comment: "1 for true. 0 for false",
    width: 1,
    default: () => "'1'",
  })
  authTokensValid: boolean;

  @Column("int", { name: "authTokens_adminId", nullable: true })
  authTokensAdminId: number | null;

  @Column("varchar", { name: "authTokens_deviceType", length: 1000 })
  authTokensDeviceType: string;

  @ManyToOne(() => Users, (users) => users.authtokens, {
    onDelete: "NO ACTION",
    onUpdate: "NO ACTION",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;

  @ManyToOne(() => Users, (users) => users.authtokens2, {
    onDelete: "NO ACTION",
    onUpdate: "NO ACTION",
  })
  @JoinColumn([
    { name: "authTokens_adminId", referencedColumnName: "usersUserid" },
  ])
  authTokensAdmin: Users;
}
