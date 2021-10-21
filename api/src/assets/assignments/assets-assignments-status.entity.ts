import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Instances } from "../../instances/instances.entity";

@Index("assetsAssignmentsStatus_instances_instances_id_fk", ["instancesId"], {})
@Entity("assetsassignmentsstatus", { schema: "adamrms" })
export class Assetsassignmentsstatus {
  @PrimaryGeneratedColumn({ type: "int", name: "assetsAssignmentsStatus_id" })
  assetsAssignmentsStatusId: number;

  @Column("int", { name: "instances_id" })
  instancesId: number;

  @Column("varchar", { name: "assetsAssignmentsStatus_name", length: 200 })
  assetsAssignmentsStatusName: string;

  @Column("int", {
    name: "assetsAssignmentsStatus_order",
    nullable: true,
    default: () => "'999'",
  })
  assetsAssignmentsStatusOrder: number | null;

  @Column("int", {
    name: "assetsAssignmentsStatus_deleted",
    default: () => "'0'",
  })
  assetsAssignmentsStatusDeleted: number;

  @ManyToOne(
    () => Instances,
    (instances) => instances.assetsassignmentsstatuses,
    { onDelete: "CASCADE", onUpdate: "CASCADE" },
  )
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;
}
