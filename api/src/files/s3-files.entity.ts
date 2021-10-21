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
import { Instances } from "../instances/instances.entity";
import { Maintenancejobsmessages } from "../maintenance/maintenance-jobs-messages.entity";
import { Modules } from "../training/modules.entity";

@Index("s3files_instances_instances_id_fk", ["instancesId"], {})
@Index("s3files_users_users_userid_fk", ["usersUserid"], {})
@Entity("s3files", { schema: "adamrms" })
export class S3files {
  @PrimaryGeneratedColumn({ type: "int", name: "s3files_id" })
  s3filesId: number;

  @Column("int", { name: "instances_id" })
  instancesId: number;

  @Column("varchar", {
    name: "s3files_path",
    nullable: true,
    comment: "NO LEADING /",
    length: 255,
  })
  s3filesPath: string | null;

  @Column("varchar", { name: "s3files_name", nullable: true, length: 1000 })
  s3filesName: string | null;

  @Column("varchar", { name: "s3files_filename", length: 255 })
  s3filesFilename: string;

  @Column("varchar", { name: "s3files_extension", length: 255 })
  s3filesExtension: string;

  @Column("varchar", {
    name: "s3files_original_name",
    nullable: true,
    comment:
      "What was this file originally called when it was uploaded? For things like file attachments\r\n",
    length: 500,
  })
  s3filesOriginalName: string | null;

  @Column("varchar", { name: "s3files_region", length: 255 })
  s3filesRegion: string;

  @Column("varchar", { name: "s3files_endpoint", length: 255 })
  s3filesEndpoint: string;

  @Column("varchar", {
    name: "s3files_cdn_endpoint",
    nullable: true,
    length: 255,
  })
  s3filesCdnEndpoint: string | null;

  @Column("varchar", { name: "s3files_bucket", length: 255 })
  s3filesBucket: string;

  @Column("bigint", {
    name: "s3files_meta_size",
    comment: "Size of the file in bytes",
  })
  s3filesMetaSize: string;

  @Column("tinyint", {
    name: "s3files_meta_public",
    width: 1,
    default: () => "'0'",
  })
  s3filesMetaPublic: boolean;

  @Column("varchar", { name: "s3files_shareKey", nullable: true, length: 255 })
  s3filesShareKey: string | null;

  @Column("tinyint", {
    name: "s3files_meta_type",
    comment: "0 = undefined\r\nRest are set out in corehead\r\n",
    default: () => "'0'",
  })
  s3filesMetaType: number;

  @Column("int", {
    name: "s3files_meta_subType",
    nullable: true,
    comment:
      "Depends what it is - each module that uses the file handler will be setting this for themselves",
  })
  s3filesMetaSubType: number | null;

  @Column("timestamp", {
    name: "s3files_meta_uploaded",
    default: () => "CURRENT_TIMESTAMP",
  })
  s3filesMetaUploaded: Date;

  @Column("int", {
    name: "users_userid",
    nullable: true,
    comment: "Who uploaded it?",
  })
  usersUserid: number | null;

  @Column("timestamp", {
    name: "s3files_meta_deleteOn",
    nullable: true,
    comment:
      "Delete this file on this set date (basically if you hit delete we will kill it after say 30 days)",
  })
  s3filesMetaDeleteOn: Date | null;

  @Column("tinyint", {
    name: "s3files_meta_physicallyStored",
    comment:
      'If we have the file it\'s 1 - if we deleted it it\'s 0 but the "deleteOn" is set. If we lost it it\'s 0 with a null "delete on"',
    width: 1,
    default: () => "'1'",
  })
  s3filesMetaPhysicallyStored: boolean;

  @Column("tinyint", {
    name: "s3files_compressed",
    width: 1,
    default: () => "'0'",
  })
  s3filesCompressed: boolean;

  @OneToMany(
    () => Maintenancejobsmessages,
    (maintenancejobsmessages) =>
      maintenancejobsmessages.maintenanceJobsMessagesFile2,
  )
  maintenancejobsmessages: Maintenancejobsmessages[];

  @OneToMany(() => Modules, (modules) => modules.modulesThumbnail2)
  modules: Modules[];

  @ManyToOne(() => Instances, (instances) => instances.s3files, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;

  @ManyToOne(() => Users, (users) => users.s3files, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;
}
