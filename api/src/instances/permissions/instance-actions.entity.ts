import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Instanceactionscategories } from "./instance-actions-categories.entity";

@Index("categories_fk", ["instanceActionsCategoriesId"], {})
@Entity("instanceactions", { schema: "adamrms" })
export class Instanceactions {
  @PrimaryGeneratedColumn({ type: "int", name: "instanceActions_id" })
  instanceActionsId: number;

  @Column("varchar", { name: "instanceActions_name", length: 255 })
  instanceActionsName: string;

  @Column("int", { name: "instanceActionsCategories_id" })
  instanceActionsCategoriesId: number;

  @Column("varchar", {
    name: "instanceActions_dependent",
    nullable: true,
    length: 200,
  })
  instanceActionsDependent: string | null;

  @Column("varchar", {
    name: "instanceActions_incompatible",
    nullable: true,
    length: 200,
  })
  instanceActionsIncompatible: string | null;

  @ManyToOne(
    () => Instanceactionscategories,
    (instanceactionscategories) => instanceactionscategories.instanceactions,
    { onDelete: "CASCADE", onUpdate: "CASCADE" },
  )
  @JoinColumn([
    {
      name: "instanceActionsCategories_id",
      referencedColumnName: "instanceActionsCategoriesId",
    },
  ])
  instanceActionsCategories: Instanceactionscategories;
}
