import { Column, Entity, OneToMany, PrimaryGeneratedColumn } from "typeorm";
import { Userpositions } from "./user-positions.entity";

@Entity("positions", { schema: "adamrms" })
export class Positions {
  @PrimaryGeneratedColumn({ type: "int", name: "positions_id" })
  positionsId: number;

  @Column("varchar", { name: "positions_displayName", length: 255 })
  positionsDisplayName: string;

  @Column("varchar", {
    name: "positions_positionsGroups",
    nullable: true,
    length: 500,
  })
  positionsPositionsGroups: string | null;

  @Column("tinyint", {
    name: "positions_rank",
    comment:
      'Rank of the position - so that the most senior position for a user is shown as their "main one". 0 is the most senior',
    default: () => "'4'",
  })
  positionsRank: number;

  @OneToMany(() => Userpositions, (userpositions) => userpositions.positions)
  userpositions: Userpositions[];
}
