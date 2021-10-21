import {
  Column,
  Entity,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Actionscategories } from "./actions-categories.entity";

@Entity("actions", { schema: "adamrms" })
export class Actions {
  @PrimaryGeneratedColumn({ type: "int", name: "actions_id" })
  actionsId: number;

  @Column("varchar", { name: "actions_name", length: 255 })
  actionsName: string;

  @Column("int", { name: "actionsCategories_id" })
  actionsCategoriesId: number;

  @Column("varchar", { name: "actions_dependent", nullable: true, length: 500 })
  actionsDependent: string | null;

  @Column("varchar", {
    name: "actions_incompatible",
    nullable: true,
    length: 500,
  })
  actionsIncompatible: string | null;

  @ManyToOne(
    () => Actionscategories,
    (actionscategories) => actionscategories.actions,
    { onDelete: "CASCADE", onUpdate: "CASCADE" },
  )
  @JoinColumn([
    {
      name: "actionsCategories_id",
      referencedColumnName: "actionsCategoriesId",
    },
  ])
  actionsCategories: Actionscategories;
}
