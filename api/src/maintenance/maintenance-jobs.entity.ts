import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Users } from "../auth/users/users.entity";
import { Maintenancejobsmessages } from "./maintenance-jobs-messages.entity";

@Index(
  "maintenanceJobs_users_users_userid_fk",
  ["maintenanceJobsUserCreator"],
  {},
)
@Entity("maintenancejobs", { schema: "adamrms" })
export class Maintenancejobs {
  @PrimaryGeneratedColumn({ type: "int", name: "maintenanceJobs_id" })
  maintenanceJobsId: number;

  @Column("varchar", { name: "maintenanceJobs_assets", length: 500 })
  maintenanceJobsAssets: string;

  @Column("timestamp", {
    name: "maintenanceJobs_timestamp_added",
    default: () => "CURRENT_TIMESTAMP",
  })
  maintenanceJobsTimestampAdded: Date;

  @Column("timestamp", {
    name: "maintenanceJobs_timestamp_due",
    nullable: true,
  })
  maintenanceJobsTimestampDue: Date | null;

  @Column("varchar", {
    name: "maintenanceJobs_user_tagged",
    nullable: true,
    length: 500,
  })
  maintenanceJobsUserTagged: string | null;

  @Column("int", { name: "maintenanceJobs_user_creator" })
  maintenanceJobsUserCreator: number;

  @Column("int", { name: "maintenanceJobs_user_assignedTo", nullable: true })
  maintenanceJobsUserAssignedTo: number | null;

  @Column("varchar", {
    name: "maintenanceJobs_title",
    nullable: true,
    length: 500,
  })
  maintenanceJobsTitle: string | null;

  @Column("varchar", {
    name: "maintenanceJobs_faultDescription",
    nullable: true,
    length: 500,
  })
  maintenanceJobsFaultDescription: string | null;

  @Column("tinyint", {
    name: "maintenanceJobs_priority",
    comment: "1 to 10",
    default: () => "'5'",
  })
  maintenanceJobsPriority: number;

  @Column("int", { name: "instances_id" })
  instancesId: number;

  @Column("tinyint", {
    name: "maintenanceJobs_deleted",
    width: 1,
    default: () => "'0'",
  })
  maintenanceJobsDeleted: boolean;

  @Column("int", { name: "maintenanceJobsStatuses_id", nullable: true })
  maintenanceJobsStatusesId: number | null;

  @Column("tinyint", {
    name: "maintenanceJobs_flagAssets",
    width: 1,
    default: () => "'0'",
  })
  maintenanceJobsFlagAssets: boolean;

  @Column("tinyint", {
    name: "maintenanceJobs_blockAssets",
    width: 1,
    default: () => "'0'",
  })
  maintenanceJobsBlockAssets: boolean;

  @ManyToOne(() => Users, (users) => users.maintenancejobs, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([
    {
      name: "maintenanceJobs_user_creator",
      referencedColumnName: "usersUserid",
    },
  ])
  maintenanceJobsUserCreator2: Users;

  @OneToMany(
    () => Maintenancejobsmessages,
    (maintenancejobsmessages) => maintenancejobsmessages.maintenanceJobs,
  )
  maintenancejobsmessages: Maintenancejobsmessages[];
}
