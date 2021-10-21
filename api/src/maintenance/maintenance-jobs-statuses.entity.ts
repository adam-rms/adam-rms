import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Instances } from "../instances/instances.entity";

@Index("maintenanceJobsStatuses_instances_instances_id_fk", ["instancesId"], {})
@Entity("maintenancejobsstatuses", { schema: "adamrms" })
export class Maintenancejobsstatuses {
  @PrimaryGeneratedColumn({ type: "int", name: "maintenanceJobsStatuses_id" })
  maintenanceJobsStatusesId: number;

  @Column("int", { name: "instances_id", nullable: true })
  instancesId: number | null;

  @Column("varchar", { name: "maintenanceJobsStatuses_name", length: 200 })
  maintenanceJobsStatusesName: string;

  @Column("tinyint", {
    name: "maintenanceJobsStatuses_order",
    width: 1,
    default: () => "'99'",
  })
  maintenanceJobsStatusesOrder: boolean;

  @Column("tinyint", {
    name: "maintenanceJobsStatuses_deleted",
    width: 1,
    default: () => "'0'",
  })
  maintenanceJobsStatusesDeleted: boolean;

  @Column("tinyint", {
    name: "maintenanceJobsStatuses_showJobInMainList",
    width: 1,
    default: () => "'1'",
  })
  maintenanceJobsStatusesShowJobInMainList: boolean;

  @ManyToOne(
    () => Instances,
    (instances) => instances.maintenancejobsstatuses,
    { onDelete: "CASCADE", onUpdate: "CASCADE" },
  )
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;
}
