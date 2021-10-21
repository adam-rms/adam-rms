import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Assetsbarcodesscans } from "../../assets/barcodes/assets-barcodes-scans.entity";
import { Users } from "../../auth/users/users.entity";

@Index("locationsBarcodes_users_users_userid_fk", ["usersUserid"], {})
@Entity("locationsbarcodes", { schema: "adamrms" })
export class Locationsbarcodes {
  @PrimaryGeneratedColumn({ type: "int", name: "locationsBarcodes_id" })
  locationsBarcodesId: number;

  @Column("int", { name: "locations_id" })
  locationsId: number;

  @Column("varchar", { name: "locationsBarcodes_value", length: 500 })
  locationsBarcodesValue: string;

  @Column("varchar", { name: "locationsBarcodes_type", length: 500 })
  locationsBarcodesType: string;

  @Column("text", { name: "locationsBarcodes_notes", nullable: true })
  locationsBarcodesNotes: string | null;

  @Column("timestamp", { name: "locationsBarcodes_added" })
  locationsBarcodesAdded: Date;

  @Column("int", {
    name: "users_userid",
    nullable: true,
    comment: "Userid that added it",
  })
  usersUserid: number | null;

  @Column("tinyint", {
    name: "locationsBarcodes_deleted",
    nullable: true,
    width: 1,
    default: () => "'0'",
  })
  locationsBarcodesDeleted: boolean | null;

  @OneToMany(
    () => Assetsbarcodesscans,
    (assetsbarcodesscans) => assetsbarcodesscans.locationsBarcodes,
  )
  assetsbarcodesscans: Assetsbarcodesscans[];

  @ManyToOne(() => Users, (users) => users.locationsbarcodes, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;
}
