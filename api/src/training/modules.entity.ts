import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Instances } from "../instances/instances.entity";
import { S3files } from "../files/s3-files.entity";
import { Users } from "../auth/users/users.entity";
import { Modulessteps } from "./modules-steps.entity";
import { Usermodules } from "./user-modules.entity";
import { Usermodulescertifications } from "../training/user-modules-certifications.entity";

@Index("modules_instances_instances_id_fk", ["instancesId"], {})
@Index("modules_users_users_userid_fk", ["usersUserid"], {})
@Index("modules_s3files_s3files_id_fk", ["modulesThumbnail"], {})
@Entity("modules", { schema: "adamrms" })
export class Modules {
  @PrimaryGeneratedColumn({ type: "int", name: "modules_id" })
  modulesId: number;

  @Column("int", { name: "instances_id" })
  instancesId: number;

  @Column("int", { name: "users_userid", comment: '"Author"' })
  usersUserid: number;

  @Column("varchar", { name: "modules_name", length: 500 })
  modulesName: string;

  @Column("text", { name: "modules_description", nullable: true })
  modulesDescription: string | null;

  @Column("text", { name: "modules_learningObjectives", nullable: true })
  modulesLearningObjectives: string | null;

  @Column("tinyint", {
    name: "modules_deleted",
    width: 1,
    default: () => "'0'",
  })
  modulesDeleted: boolean;

  @Column("tinyint", { name: "modules_show", width: 1, default: () => "'0'" })
  modulesShow: boolean;

  @Column("int", { name: "modules_thumbnail", nullable: true })
  modulesThumbnail: number | null;

  @Column("tinyint", { name: "modules_type", width: 1, default: () => "'0'" })
  modulesType: boolean;

  @ManyToOne(() => Instances, (instances) => instances.modules, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;

  @ManyToOne(() => S3files, (s3files) => s3files.modules, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([
    { name: "modules_thumbnail", referencedColumnName: "s3filesId" },
  ])
  modulesThumbnail2: S3files;

  @ManyToOne(() => Users, (users) => users.modules, {
    onDelete: "CASCADE",
    onUpdate: "NO ACTION",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;

  @OneToMany(() => Modulessteps, (modulessteps) => modulessteps.modules)
  modulessteps: Modulessteps[];

  @OneToMany(() => Usermodules, (usermodules) => usermodules.modules)
  usermodules: Usermodules[];

  @OneToMany(
    () => Usermodulescertifications,
    (usermodulescertifications) => usermodulescertifications.modules,
  )
  usermodulescertifications: Usermodulescertifications[];
}
