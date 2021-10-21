import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Assets } from "../assets.entity";
import { Assetsbarcodesscans } from "./assets-barcodes-scans.entity";
import { Users } from "../../auth/users/users.entity";

@Index("assetsBarcodes_assets_assets_id_fk", ["assetsId"], {})
@Index("assetsBarcodes_users_users_userid_fk", ["usersUserid"], {})
@Entity("assetsbarcodes", { schema: "adamrms" })
export class Assetsbarcodes {
  @PrimaryGeneratedColumn({ type: "int", name: "assetsBarcodes_id" })
  assetsBarcodesId: number;

  @Column("int", { name: "assets_id", nullable: true })
  assetsId: number | null;

  @Column("varchar", { name: "assetsBarcodes_value", length: 500 })
  assetsBarcodesValue: string;

  @Column("varchar", { name: "assetsBarcodes_type", length: 500 })
  assetsBarcodesType: string;

  @Column("text", { name: "assetsBarcodes_notes", nullable: true })
  assetsBarcodesNotes: string | null;

  @Column("timestamp", { name: "assetsBarcodes_added" })
  assetsBarcodesAdded: Date;

  @Column("int", {
    name: "users_userid",
    nullable: true,
    comment: "Userid that added it",
  })
  usersUserid: number | null;

  @Column("tinyint", {
    name: "assetsBarcodes_deleted",
    nullable: true,
    width: 1,
    default: () => "'0'",
  })
  assetsBarcodesDeleted: boolean | null;

  @ManyToOne(() => Assets, (assets) => assets.assetsbarcodes, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "assets_id", referencedColumnName: "assetsId" }])
  assets: Assets;

  @ManyToOne(() => Users, (users) => users.assetsbarcodes, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;

  @OneToMany(
    () => Assetsbarcodesscans,
    (assetsbarcodesscans) => assetsbarcodesscans.assetsBarcodes,
  )
  assetsbarcodesscans: Assetsbarcodesscans[];
}
