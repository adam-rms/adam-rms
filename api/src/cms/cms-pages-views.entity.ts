import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Users } from "../auth/users/users.entity";
import { Cmspages } from "./cms-pages.entity";

@Index("cmsPagesViews_cmsPages_cmsPages_id_fk", ["cmsPagesId"], {})
@Index("cmsPagesViews_users_users_userid_fk", ["usersUserid"], {})
@Index(
  "cmsPagesViews_cmsPagesViews_timestamp_index",
  ["cmsPagesViewsTimestamp"],
  {},
)
@Entity("cmspagesviews", { schema: "adamrms" })
export class Cmspagesviews {
  @PrimaryGeneratedColumn({ type: "int", name: "cmsPagesViews_id" })
  cmsPagesViewsId: number;

  @Column("int", { name: "cmsPages_id" })
  cmsPagesId: number;

  @Column("timestamp", {
    name: "cmsPagesViews_timestamp",
    default: () => "CURRENT_TIMESTAMP",
  })
  cmsPagesViewsTimestamp: Date;

  @Column("int", { name: "users_userid", nullable: true })
  usersUserid: number | null;

  @Column("tinyint", { name: "cmsPages_type", width: 1, default: () => "'1'" })
  cmsPagesType: boolean;

  @ManyToOne(() => Cmspages, (cmspages) => cmspages.cmspagesviews, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "cmsPages_id", referencedColumnName: "cmsPagesId" }])
  cmsPages: Cmspages;

  @ManyToOne(() => Users, (users) => users.cmspagesviews, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;
}
