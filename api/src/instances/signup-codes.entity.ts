import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Instancepositions } from "./permissions/instance-positions.entity";
import { Instances } from "./instances.entity";
import { Userinstances } from "./user-instances.entity";

@Index("signupCodes_signupCodes_name_uindex", ["signupCodesName"], {
  unique: true,
})
@Index("signupCodes_instances_instances_id_fk", ["instancesId"], {})
@Index(
  "signupCodes_instancePositions_instancePositions_id_fk",
  ["instancePositionsId"],
  {},
)
@Entity("signupcodes", { schema: "adamrms" })
export class Signupcodes {
  @PrimaryGeneratedColumn({ type: "int", name: "signupCodes_id" })
  signupCodesId: number;

  @Column("varchar", { name: "signupCodes_name", unique: true, length: 200 })
  signupCodesName: string;

  @Column("int", { name: "instances_id" })
  instancesId: number;

  @Column("tinyint", {
    name: "signupCodes_deleted",
    width: 1,
    default: () => "'0'",
  })
  signupCodesDeleted: boolean;

  @Column("tinyint", {
    name: "signupCodes_valid",
    width: 1,
    default: () => "'1'",
  })
  signupCodesValid: boolean;

  @Column("text", { name: "signupCodes_notes", nullable: true })
  signupCodesNotes: string | null;

  @Column("varchar", { name: "signupCodes_role", length: 500 })
  signupCodesRole: string;

  @Column("int", { name: "instancePositions_id", nullable: true })
  instancePositionsId: number | null;

  @ManyToOne(
    () => Instancepositions,
    (instancepositions) => instancepositions.signupcodes,
    { onDelete: "SET NULL", onUpdate: "CASCADE" },
  )
  @JoinColumn([
    {
      name: "instancePositions_id",
      referencedColumnName: "instancePositionsId",
    },
  ])
  instancePositions: Instancepositions;

  @ManyToOne(() => Instances, (instances) => instances.signupcodes, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;

  @OneToMany(() => Userinstances, (userinstances) => userinstances.signupCodes)
  userinstances: Userinstances[];
}
