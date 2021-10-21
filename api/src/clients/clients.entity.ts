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
import { Locations } from "../locations/locations.entity";
import { Projects } from "../projects/projects.entity";

@Index("clients_instances_instances_id_fk", ["instancesId"], {})
@Entity("clients", { schema: "adamrms" })
export class Clients {
  @PrimaryGeneratedColumn({ type: "int", name: "clients_id" })
  clientsId: number;

  @Column("varchar", { name: "clients_name", length: 500 })
  clientsName: string;

  @Column("int", { name: "instances_id" })
  instancesId: number;

  @Column("tinyint", {
    name: "clients_deleted",
    width: 1,
    default: () => "'0'",
  })
  clientsDeleted: boolean;

  @Column("varchar", { name: "clients_website", nullable: true, length: 500 })
  clientsWebsite: string | null;

  @Column("varchar", { name: "clients_email", nullable: true, length: 500 })
  clientsEmail: string | null;

  @Column("text", { name: "clients_notes", nullable: true })
  clientsNotes: string | null;

  @Column("varchar", { name: "clients_address", nullable: true, length: 500 })
  clientsAddress: string | null;

  @Column("varchar", { name: "clients_phone", nullable: true, length: 500 })
  clientsPhone: string | null;

  @ManyToOne(() => Instances, (instances) => instances.clients, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;

  @OneToMany(() => Locations, (locations) => locations.clients)
  locations: Locations[];

  @OneToMany(() => Projects, (projects) => projects.clients)
  projects: Projects[];
}
