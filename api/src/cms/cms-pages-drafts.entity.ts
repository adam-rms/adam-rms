import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Cmspages } from "./cms-pages.entity";
import { Users } from "../auth/users/users.entity";

@Index("cmsPagesDrafts_cmsPages_cmsPages_id_fk", ["cmsPagesId"], {})
@Index("cmsPagesDrafts_users_users_userid_fk", ["usersUserid"], {})
@Index(
  "cmsPagesDrafts_cmsPagesDrafts_timestamp_index",
  ["cmsPagesDraftsTimestamp"],
  {},
)
@Entity("cmspagesdrafts", { schema: "adamrms" })
export class Cmspagesdrafts {
  @PrimaryGeneratedColumn({ type: "int", name: "cmsPagesDrafts_id" })
  cmsPagesDraftsId: number;

  @Column("int", { name: "cmsPages_id" })
  cmsPagesId: number;

  @Column("int", { name: "users_userid", nullable: true })
  usersUserid: number | null;

  @Column("timestamp", {
    name: "cmsPagesDrafts_timestamp",
    default: () => "CURRENT_TIMESTAMP",
  })
  cmsPagesDraftsTimestamp: Date;

  @Column("json", { name: "cmsPagesDrafts_data", nullable: true })
  cmsPagesDraftsData: object | null;

  @Column("text", { name: "cmsPagesDrafts_changelog", nullable: true })
  cmsPagesDraftsChangelog: string | null;

  @Column("int", { name: "cmsPagesDrafts_revisionID" })
  cmsPagesDraftsRevisionId: number;

  @ManyToOne(() => Cmspages, (cmspages) => cmspages.cmspagesdrafts, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "cmsPages_id", referencedColumnName: "cmsPagesId" }])
  cmsPages: Cmspages;

  @ManyToOne(() => Users, (users) => users.cmspagesdrafts, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;
}
