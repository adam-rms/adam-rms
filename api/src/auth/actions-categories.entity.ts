import { Column, Entity, OneToMany, PrimaryGeneratedColumn } from "typeorm";
import { Actions } from "./actions.entity";

@Entity("actionscategories", { schema: "adamrms" })
export class Actionscategories {
  @PrimaryGeneratedColumn({ type: "int", name: "actionsCategories_id" })
  actionsCategoriesId: number;

  @Column("varchar", { name: "actionsCategories_name", length: 500 })
  actionsCategoriesName: string;

  @Column("int", {
    name: "actionsCategories_order",
    nullable: true,
    default: () => "'0'",
  })
  actionsCategoriesOrder: number | null;

  @OneToMany(() => Actions, (actions) => actions.actionsCategories)
  actions: Actions[];
}
