import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Assettypes } from "./asset-types.entity";
import { Assetsassignments } from "./assignments/assets-assignments.entity";
import { Assetsbarcodesscans } from "./barcodes/assets-barcodes-scans.entity";
import { Assetsbarcodes } from "./barcodes/assets-barcodes.entity";
import { Instances } from "../instances/instances.entity";
import { Locations } from "../locations/locations.entity";

@Index("assets_assetTypes_assetTypes_id_fk", ["assetTypesId"], {})
@Index("assets_assets_assets_id_fk", ["assetsLinkedTo"], {})
@Index("assets_instances_instances_id_fk", ["instancesId"], {})
@Index("assets_locations_locations_id_fk", ["assetsStorageLocation"], {})
@Entity("assets", { schema: "adamrms" })
export class Assets {
  @PrimaryGeneratedColumn({ type: "int", name: "assets_id" })
  assetsId: number;

  @Column("varchar", {
    name: "assets_tag",
    nullable: true,
    comment: "The ID/Tag that the asset carries marked onto it",
    length: 200,
  })
  assetsTag: string | null;

  @Column("int", { name: "assetTypes_id" })
  assetTypesId: number;

  @Column("text", { name: "assets_notes", nullable: true })
  assetsNotes: string | null;

  @Column("int", { name: "instances_id" })
  instancesId: number;

  @Column("varchar", {
    name: "asset_definableFields_1",
    nullable: true,
    length: 200,
  })
  assetDefinableFields_1: string | null;

  @Column("varchar", {
    name: "asset_definableFields_2",
    nullable: true,
    length: 200,
  })
  assetDefinableFields_2: string | null;

  @Column("varchar", {
    name: "asset_definableFields_3",
    nullable: true,
    length: 200,
  })
  assetDefinableFields_3: string | null;

  @Column("varchar", {
    name: "asset_definableFields_4",
    nullable: true,
    length: 200,
  })
  assetDefinableFields_4: string | null;

  @Column("varchar", {
    name: "asset_definableFields_5",
    nullable: true,
    length: 200,
  })
  assetDefinableFields_5: string | null;

  @Column("varchar", {
    name: "asset_definableFields_6",
    nullable: true,
    length: 200,
  })
  assetDefinableFields_6: string | null;

  @Column("varchar", {
    name: "asset_definableFields_7",
    nullable: true,
    length: 200,
  })
  assetDefinableFields_7: string | null;

  @Column("varchar", {
    name: "asset_definableFields_8",
    nullable: true,
    length: 200,
  })
  assetDefinableFields_8: string | null;

  @Column("varchar", {
    name: "asset_definableFields_9",
    nullable: true,
    length: 200,
  })
  assetDefinableFields_9: string | null;

  @Column("varchar", {
    name: "asset_definableFields_10",
    nullable: true,
    length: 200,
  })
  assetDefinableFields_10: string | null;

  @Column("timestamp", {
    name: "assets_inserted",
    default: () => "CURRENT_TIMESTAMP",
  })
  assetsInserted: Date;

  @Column("int", { name: "assets_dayRate", nullable: true })
  assetsDayRate: number | null;

  @Column("int", { name: "assets_linkedTo", nullable: true })
  assetsLinkedTo: number | null;

  @Column("int", { name: "assets_weekRate", nullable: true })
  assetsWeekRate: number | null;

  @Column("int", { name: "assets_value", nullable: true })
  assetsValue: number | null;

  @Column("decimal", {
    name: "assets_mass",
    nullable: true,
    precision: 55,
    scale: 5,
  })
  assetsMass: string | null;

  @Column("tinyint", { name: "assets_deleted", width: 1, default: () => "'0'" })
  assetsDeleted: boolean;

  @Column("timestamp", { name: "assets_endDate", nullable: true })
  assetsEndDate: Date | null;

  @Column("varchar", { name: "assets_archived", nullable: true, length: 200 })
  assetsArchived: string | null;

  @Column("varchar", {
    name: "assets_assetGroups",
    nullable: true,
    length: 500,
  })
  assetsAssetGroups: string | null;

  @Column("int", { name: "assets_storageLocation", nullable: true })
  assetsStorageLocation: number | null;

  @Column("tinyint", {
    name: "assets_showPublic",
    width: 1,
    default: () => "'1'",
  })
  assetsShowPublic: boolean;

  @ManyToOne(() => Assets, (assets) => assets.assets, {
    onDelete: "SET NULL",
    onUpdate: "SET NULL",
  })
  @JoinColumn([{ name: "assets_linkedTo", referencedColumnName: "assetsId" }])
  assetsLinkedTo2: Assets;

  @OneToMany(() => Assets, (assets) => assets.assetsLinkedTo2)
  assets: Assets[];

  @ManyToOne(() => Assettypes, (assettypes) => assettypes.assets, {
    onDelete: "NO ACTION",
    onUpdate: "NO ACTION",
  })
  @JoinColumn([{ name: "assetTypes_id", referencedColumnName: "assetTypesId" }])
  assetTypes: Assettypes;

  @ManyToOne(() => Instances, (instances) => instances.assets, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;

  @ManyToOne(() => Locations, (locations) => locations.assets, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([
    { name: "assets_storageLocation", referencedColumnName: "locationsId" },
  ])
  assetsStorageLocation2: Locations;

  @OneToMany(
    () => Assetsassignments,
    (assetsassignments) => assetsassignments.assets,
  )
  assetsassignments: Assetsassignments[];

  @OneToMany(() => Assetsbarcodes, (assetsbarcodes) => assetsbarcodes.assets)
  assetsbarcodes: Assetsbarcodes[];

  @OneToMany(
    () => Assetsbarcodesscans,
    (assetsbarcodesscans) => assetsbarcodesscans.locationAssets,
  )
  assetsbarcodesscans: Assetsbarcodesscans[];
}
