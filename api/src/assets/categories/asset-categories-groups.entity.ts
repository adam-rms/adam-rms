import { Column, Entity, OneToMany, PrimaryGeneratedColumn } from "typeorm";
import { Assetcategories } from "./asset-categories.entity";

@Entity("assetcategoriesgroups", { schema: "adamrms" })
export class Assetcategoriesgroups {
  @PrimaryGeneratedColumn({ type: "int", name: "assetCategoriesGroups_id" })
  assetCategoriesGroupsId: number;

  @Column("varchar", { name: "assetCategoriesGroups_name", length: 200 })
  assetCategoriesGroupsName: string;

  @Column("varchar", {
    name: "assetCategoriesGroups_fontAwesome",
    nullable: true,
    length: 300,
  })
  assetCategoriesGroupsFontAwesome: string | null;

  @Column("int", {
    name: "assetCategoriesGroups_order",
    default: () => "'999'",
  })
  assetCategoriesGroupsOrder: number;

  @OneToMany(
    () => Assetcategories,
    (assetcategories) => assetcategories.assetCategoriesGroups,
  )
  assetcategories: Assetcategories[];
}
