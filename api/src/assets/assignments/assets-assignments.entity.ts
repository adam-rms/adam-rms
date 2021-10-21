import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Assets } from "../assets.entity";
import { Projects } from "./Projects";

@Index("assetsAssignments_assets_assets_id_fk", ["assetsId"], {})
@Index("assetsAssignments_projects_projects_id_fk", ["projectsId"], {})
@Index(
  "assetsAssignments_assetsAssignments_assetsAssignments_id_fk",
  ["assetsAssignmentsLinkedTo"],
  {},
)
@Entity("assetsassignments", { schema: "adamrms" })
export class Assetsassignments {
  @PrimaryGeneratedColumn({ type: "int", name: "assetsAssignments_id" })
  assetsAssignmentsId: number;

  @Column("int", { name: "assets_id" })
  assetsId: number;

  @Column("int", { name: "projects_id" })
  projectsId: number;

  @Column("varchar", {
    name: "assetsAssignments_comment",
    nullable: true,
    length: 500,
  })
  assetsAssignmentsComment: string | null;

  @Column("int", {
    name: "assetsAssignments_customPrice",
    default: () => "'0'",
  })
  assetsAssignmentsCustomPrice: number;

  @Column("float", {
    name: "assetsAssignments_discount",
    precision: 12,
    default: () => "'0'",
  })
  assetsAssignmentsDiscount: number;

  @Column("timestamp", { name: "assetsAssignments_timestamp", nullable: true })
  assetsAssignmentsTimestamp: Date | null;

  @Column("tinyint", {
    name: "assetsAssignments_deleted",
    width: 1,
    default: () => "'0'",
  })
  assetsAssignmentsDeleted: boolean;

  @Column("int", {
    name: "assetsAssignmentsStatus_id",
    nullable: true,
    comment:
      "0 = None applicable\r\n10 = Pending pick\r\n20 = Picked\r\n30 = Prepping\r\n40 = Tested\r\n50 = Packed\r\n60 = Dispatched\r\n70 = Awaiting Check-in\r\n80 = Case opened\r\n90 = Unpacked\r\n100 = Tested\r\n110 = Stored",
  })
  assetsAssignmentsStatusId: number | null;

  @Column("int", { name: "assetsAssignments_linkedTo", nullable: true })
  assetsAssignmentsLinkedTo: number | null;

  @ManyToOne(
    () => Assetsassignments,
    (assetsassignments) => assetsassignments.assetsassignments,
    { onDelete: "CASCADE", onUpdate: "CASCADE" },
  )
  @JoinColumn([
    {
      name: "assetsAssignments_linkedTo",
      referencedColumnName: "assetsAssignmentsId",
    },
  ])
  assetsAssignmentsLinkedTo2: Assetsassignments;

  @OneToMany(
    () => Assetsassignments,
    (assetsassignments) => assetsassignments.assetsAssignmentsLinkedTo2,
  )
  assetsassignments: Assetsassignments[];

  @ManyToOne(() => Assets, (assets) => assets.assetsassignments, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "assets_id", referencedColumnName: "assetsId" }])
  assets: Assets;

  @ManyToOne(() => Projects, (projects) => projects.assetsassignments, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "projects_id", referencedColumnName: "projectsId" }])
  projects: Projects;
}
