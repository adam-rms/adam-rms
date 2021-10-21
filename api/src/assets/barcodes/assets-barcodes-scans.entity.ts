import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Assets } from "../assets.entity";
import { Assetsbarcodes } from "./assets-barcodes.entity";
import { Locationsbarcodes } from "./Locationsbarcodes";
import { Users } from "../auth/users/users.entity";

@Index(
  "assetsBarcodesScans_assetsBarcodes_assetsBarcodes_id_fk",
  ["assetsBarcodesId"],
  {},
)
@Index("assetsBarcodesScans_users_users_userid_fk", ["usersUserid"], {})
@Index(
  "assetsBarcodesScans_locationsBarcodes_locationsBarcodes_id_fk",
  ["locationsBarcodesId"],
  {},
)
@Index("assetsBarcodesScans_assets_assets_id_fk", ["locationAssetsId"], {})
@Entity("assetsbarcodesscans", { schema: "adamrms" })
export class Assetsbarcodesscans {
  @PrimaryGeneratedColumn({ type: "int", name: "assetsBarcodesScans_id" })
  assetsBarcodesScansId: number;

  @Column("int", { name: "assetsBarcodes_id" })
  assetsBarcodesId: number;

  @Column("timestamp", { name: "assetsBarcodesScans_timestamp" })
  assetsBarcodesScansTimestamp: Date;

  @Column("int", { name: "users_userid", nullable: true })
  usersUserid: number | null;

  @Column("int", { name: "locationsBarcodes_id", nullable: true })
  locationsBarcodesId: number | null;

  @Column("int", { name: "location_assets_id", nullable: true })
  locationAssetsId: number | null;

  @Column("varchar", {
    name: "assetsBarcodes_customLocation",
    nullable: true,
    length: 500,
  })
  assetsBarcodesCustomLocation: string | null;

  @ManyToOne(
    () => Assetsbarcodes,
    (assetsbarcodes) => assetsbarcodes.assetsbarcodesscans,
    { onDelete: "CASCADE", onUpdate: "CASCADE" },
  )
  @JoinColumn([
    { name: "assetsBarcodes_id", referencedColumnName: "assetsBarcodesId" },
  ])
  assetsBarcodes: Assetsbarcodes;

  @ManyToOne(() => Assets, (assets) => assets.assetsbarcodesscans, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([
    { name: "location_assets_id", referencedColumnName: "assetsId" },
  ])
  locationAssets: Assets;

  @ManyToOne(
    () => Locationsbarcodes,
    (locationsbarcodes) => locationsbarcodes.assetsbarcodesscans,
    { onDelete: "CASCADE", onUpdate: "CASCADE" },
  )
  @JoinColumn([
    {
      name: "locationsBarcodes_id",
      referencedColumnName: "locationsBarcodesId",
    },
  ])
  locationsBarcodes: Locationsbarcodes;

  @ManyToOne(() => Users, (users) => users.assetsbarcodesscans, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;
}
