import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Assets } from "../assets/assets.entity";
import { Clients } from "../clients/clients.entity";
import { Instances } from "../instances/instances.entity";
import { Projects } from "../projects/projects.entity";

@Index("locations_clients_clients_id_fk", ["clientsId"], {})
@Index("locations_instances_instances_id_fk", ["instancesId"], {})
@Index("locations_locations_locations_id_fk", ["locationsSubOf"], {})
@Entity("locations", { schema: "adamrms" })
export class Locations {
  @PrimaryGeneratedColumn({ type: "int", name: "locations_id" })
  locationsId: number;

  @Column("varchar", { name: "locations_name", length: 500 })
  locationsName: string;

  @Column("int", { name: "clients_id", nullable: true })
  clientsId: number | null;

  @Column("int", { name: "instances_id" })
  instancesId: number;

  @Column("text", { name: "locations_address", nullable: true })
  locationsAddress: string | null;

  @Column("tinyint", {
    name: "locations_deleted",
    width: 1,
    default: () => "'0'",
  })
  locationsDeleted: boolean;

  @Column("int", { name: "locations_subOf", nullable: true })
  locationsSubOf: number | null;

  @Column("text", { name: "locations_notes", nullable: true })
  locationsNotes: string | null;

  @OneToMany(() => Assets, (assets) => assets.assetsStorageLocation2)
  assets: Assets[];

  @ManyToOne(() => Clients, (clients) => clients.locations, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "clients_id", referencedColumnName: "clientsId" }])
  clients: Clients;

  @ManyToOne(() => Instances, (instances) => instances.locations, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;

  @ManyToOne(() => Locations, (locations) => locations.locations, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([
    { name: "locations_subOf", referencedColumnName: "locationsId" },
  ])
  locationsSubOf2: Locations;

  @OneToMany(() => Locations, (locations) => locations.locationsSubOf2)
  locations: Locations[];

  @OneToMany(() => Projects, (projects) => projects.locations)
  projects: Projects[];
}
