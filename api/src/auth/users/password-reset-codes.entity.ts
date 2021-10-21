import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Users } from "./users.entity";

@Index("passwordResetCodes_users_users_userid_fk", ["usersUserid"], {})
@Entity("passwordresetcodes", { schema: "adamrms" })
export class Passwordresetcodes {
  @PrimaryGeneratedColumn({ type: "int", name: "passwordResetCodes_id" })
  passwordResetCodesId: number;

  @Column("varchar", { name: "passwordResetCodes_code", length: 1000 })
  passwordResetCodesCode: string;

  @Column("tinyint", {
    name: "passwordResetCodes_used",
    width: 1,
    default: () => "'0'",
  })
  passwordResetCodesUsed: boolean;

  @Column("timestamp", {
    name: "passwordResetCodes_timestamp",
    default: () => "CURRENT_TIMESTAMP",
  })
  passwordResetCodesTimestamp: Date;

  @Column("int", { name: "passwordResetCodes_valid", default: () => "'1'" })
  passwordResetCodesValid: number;

  @Column("int", { name: "users_userid" })
  usersUserid: number;

  @ManyToOne(() => Users, (users) => users.passwordresetcodes, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;
}
