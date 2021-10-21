import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Instances } from "../../instances/instances.entity";
import { Users } from "../../auth/users/users.entity";

@Index("assetGroups_instances_instances_id_fk", ["instancesId"], {})
@Index("assetGroups_users_users_userid_fk", ["usersUserid"], {})
@Entity("assetgroups", { schema: "adamrms" })
export class Assetgroups {
  @PrimaryGeneratedColumn({ type: "int", name: "assetGroups_id" })
  assetGroupsId: number;

  @Column("varchar", { name: "assetGroups_name", length: 200 })
  assetGroupsName: string;

  @Column("text", { name: "assetGroups_description", nullable: true })
  assetGroupsDescription: string | null;

  @Column("tinyint", {
    name: "assetGroups_deleted",
    width: 1,
    default: () => "'0'",
  })
  assetGroupsDeleted: boolean;

  @Column("int", { name: "users_userid", nullable: true })
  usersUserid: number | null;

  @Column("int", { name: "instances_id" })
  instancesId: number;

  @ManyToOne(() => Instances, (instances) => instances.assetgroups, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;

  @ManyToOne(() => Users, (users) => users.assetgroups, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;
}
