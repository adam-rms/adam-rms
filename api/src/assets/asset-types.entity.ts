import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Assetcategories } from "../assets/categories/asset-categories.entity";
import { Assets } from "./assets.entity";
import { Instances } from "../instances/instances.entity";
import { Manufacturers } from "../assets/manufacturers.entity";

@Index(
  "assetTypes_assetCategories_assetCategories_id_fk",
  ["assetCategoriesId"],
  {},
)
@Index("assetTypes_manufacturers_manufacturers_id_fk", ["manufacturersId"], {})
@Index("assetTypes_instances_instances_id_fk", ["instancesId"], {})
@Entity("assettypes", { schema: "adamrms" })
export class Assettypes {
  @PrimaryGeneratedColumn({ type: "int", name: "assetTypes_id" })
  assetTypesId: number;

  @Column("varchar", { name: "assetTypes_name", length: 500 })
  assetTypesName: string;

  @Column("int", { name: "assetCategories_id" })
  assetCategoriesId: number;

  @Column("int", { name: "manufacturers_id" })
  manufacturersId: number;

  @Column("int", { name: "instances_id", nullable: true })
  instancesId: number | null;

  @Column("varchar", {
    name: "assetTypes_description",
    nullable: true,
    length: 1000,
  })
  assetTypesDescription: string | null;

  @Column("varchar", {
    name: "assetTypes_productLink",
    nullable: true,
    length: 500,
  })
  assetTypesProductLink: string | null;

  @Column("varchar", {
    name: "assetTypes_definableFields",
    nullable: true,
    length: 500,
  })
  assetTypesDefinableFields: string | null;

  @Column("decimal", {
    name: "assetTypes_mass",
    nullable: true,
    precision: 55,
    scale: 5,
  })
  assetTypesMass: string | null;

  @Column("timestamp", { name: "assetTypes_inserted", nullable: true })
  assetTypesInserted: Date | null;

  @Column("int", { name: "assetTypes_dayRate" })
  assetTypesDayRate: number;

  @Column("int", { name: "assetTypes_weekRate" })
  assetTypesWeekRate: number;

  @Column("int", { name: "assetTypes_value" })
  assetTypesValue: number;

  @OneToMany(() => Assets, (assets) => assets.assetTypes)
  assets: Assets[];

  @ManyToOne(
    () => Assetcategories,
    (assetcategories) => assetcategories.assettypes,
    { onDelete: "CASCADE", onUpdate: "CASCADE" },
  )
  @JoinColumn([
    { name: "assetCategories_id", referencedColumnName: "assetCategoriesId" },
  ])
  assetCategories: Assetcategories;

  @ManyToOne(() => Instances, (instances) => instances.assettypes, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;

  @ManyToOne(() => Manufacturers, (manufacturers) => manufacturers.assettypes, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([
    { name: "manufacturers_id", referencedColumnName: "manufacturersId" },
  ])
  manufacturers: Manufacturers;
}
