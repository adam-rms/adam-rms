import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Instances } from "../instances.entity";
import { Signupcodes } from "../signup-codes.entity";
import { Userinstances } from "../user-instances.entity";

@Index("instancePositions_instances_instances_id_fk", ["instancesId"], {})
@Entity("instancepositions", { schema: "adamrms" })
export class Instancepositions {
  @PrimaryGeneratedColumn({ type: "int", name: "instancePositions_id" })
  instancePositionsId: number;

  @Column("int", { name: "instances_id" })
  instancesId: number;

  @Column("varchar", { name: "instancePositions_displayName", length: 500 })
  instancePositionsDisplayName: string;

  @Column("int", { name: "instancePositions_rank", default: () => "'999'" })
  instancePositionsRank: number;

  @Column("varchar", {
    name: "instancePositions_actions",
    nullable: true,
    length: 5000,
  })
  instancePositionsActions: string | null;

  @Column("tinyint", {
    name: "instancePositions_deleted",
    width: 1,
    default: () => "'0'",
  })
  instancePositionsDeleted: boolean;

  @ManyToOne(() => Instances, (instances) => instances.instancepositions, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;

  @OneToMany(() => Signupcodes, (signupcodes) => signupcodes.instancePositions)
  signupcodes: Signupcodes[];

  @OneToMany(
    () => Userinstances,
    (userinstances) => userinstances.instancePositions,
  )
  userinstances: Userinstances[];
}
