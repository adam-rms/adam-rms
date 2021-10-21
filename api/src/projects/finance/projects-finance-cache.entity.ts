import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Projects } from "../projects.entity";

@Index("projectsFinanceCache_projects_projects_id_fk", ["projectsId"], {})
@Index("projectFinnaceCacheTimestamp", ["projectsFinanceCacheTimestamp"], {})
@Entity("projectsfinancecache", { schema: "adamrms" })
export class Projectsfinancecache {
  @PrimaryGeneratedColumn({ type: "int", name: "projectsFinanceCache_id" })
  projectsFinanceCacheId: number;

  @Column("int", { name: "projects_id" })
  projectsId: number;

  @Column("timestamp", { name: "projectsFinanceCache_timestamp" })
  projectsFinanceCacheTimestamp: Date;

  @Column("timestamp", {
    name: "projectsFinanceCache_timestampUpdated",
    nullable: true,
  })
  projectsFinanceCacheTimestampUpdated: Date | null;

  @Column("int", {
    name: "projectsFinanceCache_equipmentSubTotal",
    nullable: true,
  })
  projectsFinanceCacheEquipmentSubTotal: number | null;

  @Column("int", {
    name: "projectsFinanceCache_equiptmentDiscounts",
    nullable: true,
  })
  projectsFinanceCacheEquiptmentDiscounts: number | null;

  @Column("int", {
    name: "projectsFinanceCache_equiptmentTotal",
    nullable: true,
  })
  projectsFinanceCacheEquiptmentTotal: number | null;

  @Column("int", { name: "projectsFinanceCache_salesTotal", nullable: true })
  projectsFinanceCacheSalesTotal: number | null;

  @Column("int", { name: "projectsFinanceCache_staffTotal", nullable: true })
  projectsFinanceCacheStaffTotal: number | null;

  @Column("int", {
    name: "projectsFinanceCache_externalHiresTotal",
    nullable: true,
  })
  projectsFinanceCacheExternalHiresTotal: number | null;

  @Column("int", {
    name: "projectsFinanceCache_paymentsReceived",
    nullable: true,
  })
  projectsFinanceCachePaymentsReceived: number | null;

  @Column("int", { name: "projectsFinanceCache_grandTotal", nullable: true })
  projectsFinanceCacheGrandTotal: number | null;

  @Column("int", { name: "projectsFinanceCache_value", nullable: true })
  projectsFinanceCacheValue: number | null;

  @Column("decimal", {
    name: "projectsFinanceCache_mass",
    nullable: true,
    precision: 55,
    scale: 5,
  })
  projectsFinanceCacheMass: string | null;

  @ManyToOne(() => Projects, (projects) => projects.projectsfinancecaches, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "projects_id", referencedColumnName: "projectsId" }])
  projects: Projects;
}
