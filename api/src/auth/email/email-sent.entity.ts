import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Users } from "../users/users.entity";

@Index("emailSent_users_users_userid_fk", ["usersUserid"], {})
@Entity("emailsent", { schema: "adamrms" })
export class Emailsent {
  @PrimaryGeneratedColumn({ type: "int", name: "emailSent_id" })
  emailSentId: number;

  @Column("int", { name: "users_userid" })
  usersUserid: number;

  @Column("longtext", { name: "emailSent_html" })
  emailSentHtml: string;

  @Column("varchar", { name: "emailSent_subject", length: 255 })
  emailSentSubject: string;

  @Column("timestamp", {
    name: "emailSent_sent",
    default: () => "CURRENT_TIMESTAMP",
  })
  emailSentSent: Date;

  @Column("varchar", { name: "emailSent_fromEmail", length: 200 })
  emailSentFromEmail: string;

  @Column("varchar", { name: "emailSent_fromName", length: 200 })
  emailSentFromName: string;

  @Column("varchar", { name: "emailSent_toName", length: 200 })
  emailSentToName: string;

  @Column("varchar", { name: "emailSent_toEmail", length: 200 })
  emailSentToEmail: string;

  @ManyToOne(() => Users, (users) => users.emailsents, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;
}
