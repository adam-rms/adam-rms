import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Projects } from "../projects.entity";

@Index("payments_projects_projects_id_fk", ["projectsId"], {})
@Entity("payments", { schema: "adamrms" })
export class Payments {
  @PrimaryGeneratedColumn({ type: "int", name: "payments_id" })
  paymentsId: number;

  @Column("int", { name: "payments_amount" })
  paymentsAmount: number;

  @Column("int", { name: "payments_quantity", default: () => "'1'" })
  paymentsQuantity: number;

  @Column("tinyint", {
    name: "payments_type",
    comment:
      "1 = Payment Recieved\r\n2 = Sales item\r\n3 = SubHire item\r\n4 = Staff cost",
    width: 1,
  })
  paymentsType: boolean;

  @Column("varchar", {
    name: "payments_reference",
    nullable: true,
    length: 500,
  })
  paymentsReference: string | null;

  @Column("timestamp", {
    name: "payments_date",
    default: () => "CURRENT_TIMESTAMP",
  })
  paymentsDate: Date;

  @Column("varchar", { name: "payments_supplier", nullable: true, length: 500 })
  paymentsSupplier: string | null;

  @Column("varchar", { name: "payments_method", nullable: true, length: 500 })
  paymentsMethod: string | null;

  @Column("varchar", { name: "payments_comment", nullable: true, length: 500 })
  paymentsComment: string | null;

  @Column("int", { name: "projects_id" })
  projectsId: number;

  @Column("tinyint", {
    name: "payments_deleted",
    width: 1,
    default: () => "'0'",
  })
  paymentsDeleted: boolean;

  @ManyToOne(() => Projects, (projects) => projects.payments, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "projects_id", referencedColumnName: "projectsId" }])
  projects: Projects;
}
