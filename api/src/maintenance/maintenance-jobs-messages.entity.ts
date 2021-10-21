import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Maintenancejobs } from "./maintenance-jobs.entity";
import { S3files } from "../files/s3-files.entity";

@Index("maintenanceJobsMessages___files", ["maintenanceJobsMessagesFile"], {})
@Index(
  "maintenanceJobsMessages_maintenanceJobs_maintenanceJobs_id_fk",
  ["maintenanceJobsId"],
  {},
)
@Entity("maintenancejobsmessages", { schema: "adamrms" })
export class Maintenancejobsmessages {
  @PrimaryGeneratedColumn({ type: "int", name: "maintenanceJobsMessages_id" })
  maintenanceJobsMessagesId: number;

  @Column("int", { name: "maintenanceJobs_id", nullable: true })
  maintenanceJobsId: number | null;

  @Column("timestamp", {
    name: "maintenanceJobsMessages_timestamp",
    default: () => "CURRENT_TIMESTAMP",
  })
  maintenanceJobsMessagesTimestamp: Date;

  @Column("int", { name: "users_userid" })
  usersUserid: number;

  @Column("tinyint", {
    name: "maintenanceJobsMessages_deleted",
    width: 1,
    default: () => "'0'",
  })
  maintenanceJobsMessagesDeleted: boolean;

  @Column("text", { name: "maintenanceJobsMessages_text", nullable: true })
  maintenanceJobsMessagesText: string | null;

  @Column("int", { name: "maintenanceJobsMessages_file", nullable: true })
  maintenanceJobsMessagesFile: number | null;

  @ManyToOne(
    () => Maintenancejobs,
    (maintenancejobs) => maintenancejobs.maintenancejobsmessages,
    { onDelete: "CASCADE", onUpdate: "CASCADE" },
  )
  @JoinColumn([
    { name: "maintenanceJobs_id", referencedColumnName: "maintenanceJobsId" },
  ])
  maintenanceJobs: Maintenancejobs;

  @ManyToOne(() => S3files, (s3files) => s3files.maintenancejobsmessages, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([
    { name: "maintenanceJobsMessages_file", referencedColumnName: "s3filesId" },
  ])
  maintenanceJobsMessagesFile2: S3files;
}
