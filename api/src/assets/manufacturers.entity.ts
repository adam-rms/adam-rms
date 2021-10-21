import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Assettypes } from "./Assettypes";
import { Instances } from "./Instances";

@Index("manufacturers_instances_instances_id_fk", ["instancesId"], {})
@Entity("manufacturers", { schema: "adamrms" })
export class Manufacturers {
  @PrimaryGeneratedColumn({ type: "int", name: "manufacturers_id" })
  manufacturersId: number;

  @Column("varchar", { name: "manufacturers_name", length: 500 })
  manufacturersName: string;

  @Column("int", { name: "instances_id", nullable: true })
  instancesId: number | null;

  @Column("varchar", {
    name: "manufacturers_internalAdamRMSNote",
    nullable: true,
    length: 500,
  })
  manufacturersInternalAdamRmsNote: string | null;

  @Column("varchar", {
    name: "manufacturers_website",
    nullable: true,
    length: 200,
  })
  manufacturersWebsite: string | null;

  @Column("text", { name: "manufacturers_notes", nullable: true })
  manufacturersNotes: string | null;

  @OneToMany(() => Assettypes, (assettypes) => assettypes.manufacturers)
  assettypes: Assettypes[];

  @ManyToOne(() => Instances, (instances) => instances.manufacturers, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;
}
