import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Users } from "./users.entity";

@Index("emailVerificationCodes_users_users_userid_fk", ["usersUserid"], {})
@Entity("emailverificationcodes", { schema: "adamrms" })
export class Emailverificationcodes {
  @PrimaryGeneratedColumn({ type: "int", name: "emailVerificationCodes_id" })
  emailVerificationCodesId: number;

  @Column("varchar", { name: "emailVerificationCodes_code", length: 1000 })
  emailVerificationCodesCode: string;

  @Column("tinyint", {
    name: "emailVerificationCodes_used",
    width: 1,
    default: () => "'0'",
  })
  emailVerificationCodesUsed: boolean;

  @Column("timestamp", {
    name: "emailVerificationCodes_timestamp",
    default: () => "CURRENT_TIMESTAMP",
  })
  emailVerificationCodesTimestamp: Date;

  @Column("int", { name: "emailVerificationCodes_valid", default: () => "'1'" })
  emailVerificationCodesValid: number;

  @Column("int", { name: "users_userid" })
  usersUserid: number;

  @ManyToOne(() => Users, (users) => users.emailverificationcodes, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;
}
