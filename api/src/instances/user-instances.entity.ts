import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Users } from "../auth/users/users.entity";
import { Instancepositions } from "./permissions/instance-positions.entity";
import { Signupcodes } from "./signup-codes.entity";

@Index(
  "userInstances_instancePositions_instancePositions_id_fk",
  ["instancePositionsId"],
  {},
)
@Index("userInstances_users_users_userid_fk", ["usersUserid"], {})
@Index("userInstances_signupCodes_signupCodes_id_fk", ["signupCodesId"], {})
@Entity("userinstances", { schema: "adamrms" })
export class Userinstances {
  @PrimaryGeneratedColumn({ type: "int", name: "userInstances_id" })
  userInstancesId: number;

  @Column("int", { name: "users_userid" })
  usersUserid: number;

  @Column("int", { name: "instancePositions_id" })
  instancePositionsId: number;

  @Column("varchar", {
    name: "userInstances_extraPermissions",
    nullable: true,
    length: 5000,
  })
  userInstancesExtraPermissions: string | null;

  @Column("varchar", {
    name: "userInstances_label",
    nullable: true,
    length: 500,
  })
  userInstancesLabel: string | null;

  @Column("tinyint", {
    name: "userInstances_deleted",
    width: 1,
    default: () => "'0'",
  })
  userInstancesDeleted: boolean;

  @Column("int", { name: "signupCodes_id", nullable: true })
  signupCodesId: number | null;

  @Column("timestamp", { name: "userInstances_archived", nullable: true })
  userInstancesArchived: Date | null;

  @ManyToOne(
    () => Instancepositions,
    (instancepositions) => instancepositions.userinstances,
    { onDelete: "CASCADE", onUpdate: "CASCADE" },
  )
  @JoinColumn([
    {
      name: "instancePositions_id",
      referencedColumnName: "instancePositionsId",
    },
  ])
  instancePositions: Instancepositions;

  @ManyToOne(() => Signupcodes, (signupcodes) => signupcodes.userinstances, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([
    { name: "signupCodes_id", referencedColumnName: "signupCodesId" },
  ])
  signupCodes: Signupcodes;

  @ManyToOne(() => Users, (users) => users.userinstances, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;
}
