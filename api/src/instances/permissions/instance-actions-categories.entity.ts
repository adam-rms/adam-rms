import { Column, Entity, OneToMany, PrimaryGeneratedColumn } from "typeorm";
import { Instanceactions } from "./instance-actions.entity";

@Entity("instanceactionscategories", { schema: "adamrms" })
export class Instanceactionscategories {
  @PrimaryGeneratedColumn({ type: "int", name: "instanceActionsCategories_id" })
  instanceActionsCategoriesId: number;

  @Column("varchar", { name: "instanceActionsCategories_name", length: 255 })
  instanceActionsCategoriesName: string;

  @Column("int", {
    name: "instanceActionsCategories_order",
    default: 999,
  })
  instanceActionsCategoriesOrder: number;

  @OneToMany(
    () => Instanceactions,
    (instanceactions) => instanceactions.instanceActionsCategories,
  )
  instanceactions: Instanceactions[];
}
