import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Assetcategoriesgroups } from "./asset-categories-groups.entity";
import { Instances } from "../instances/Instances";
import { Assettypes } from "./Assettypes";

@Index("assetCategories_instances_instances_id_fk", ["instancesId"], {})
@Index("assetCategories_Groups_id_fk", ["assetCategoriesGroupsId"], {})
@Entity("assetcategories", { schema: "adamrms" })
export class Assetcategories {
  @PrimaryGeneratedColumn({ type: "int", name: "assetCategories_id" })
  assetCategoriesId: number;

  @Column("varchar", { name: "assetCategories_name", length: 200 })
  assetCategoriesName: string;

  @Column("varchar", {
    name: "assetCategories_fontAwesome",
    nullable: true,
    length: 100,
  })
  assetCategoriesFontAwesome: string | null;

  @Column("int", { name: "assetCategories_rank", default: () => "'999'" })
  assetCategoriesRank: number;

  @Column("int", { name: "assetCategoriesGroups_id" })
  assetCategoriesGroupsId: number;

  @Column("int", { name: "instances_id", nullable: true })
  instancesId: number | null;

  @Column("tinyint", {
    name: "assetCategories_deleted",
    width: 1,
    default: () => "'0'",
  })
  assetCategoriesDeleted: boolean;

  @ManyToOne(
    () => Assetcategoriesgroups,
    (assetcategoriesgroups) => assetcategoriesgroups.assetcategories,
    { onDelete: "CASCADE", onUpdate: "CASCADE" },
  )
  @JoinColumn([
    {
      name: "assetCategoriesGroups_id",
      referencedColumnName: "assetCategoriesGroupsId",
    },
  ])
  assetCategoriesGroups: Assetcategoriesgroups;

  @ManyToOne(() => Instances, (instances) => instances.assetcategories, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;

  @OneToMany(() => Assettypes, (assettypes) => assettypes.assetCategories)
  assettypes: Assettypes[];
}
