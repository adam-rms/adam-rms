import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Positions } from "./positions.entity";
import { Users } from "./users/users.entity";

@Index("userPositions_positions_positions_id_fk", ["positionsId"], {})
@Index("userPositions_users_users_userid_fk", ["usersUserid"], {})
@Entity("userpositions", { schema: "adamrms" })
export class Userpositions {
  @PrimaryGeneratedColumn({ type: "int", name: "userPositions_id" })
  userPositionsId: number;

  @Column("int", { name: "users_userid", nullable: true })
  usersUserid: number | null;

  @Column("timestamp", {
    name: "userPositions_start",
    default: () => "CURRENT_TIMESTAMP",
  })
  userPositionsStart: Date;

  @Column("timestamp", { name: "userPositions_end", nullable: true })
  userPositionsEnd: Date | null;

  @Column("int", {
    name: "positions_id",
    nullable: true,
    comment:
      "Can be null if you like - as long as you set the relevant other fields",
  })
  positionsId: number | null;

  @Column("varchar", {
    name: "userPositions_displayName",
    nullable: true,
    length: 255,
  })
  userPositionsDisplayName: string | null;

  @Column("varchar", {
    name: "userPositions_extraPermissions",
    nullable: true,
    comment:
      "Allow a few extra permissions to be added just for this user for that exact permissions term\r\n",
    length: 500,
  })
  userPositionsExtraPermissions: string | null;

  @Column("tinyint", {
    name: "userPositions_show",
    width: 1,
    default: () => "'1'",
  })
  userPositionsShow: boolean;

  @ManyToOne(() => Positions, (positions) => positions.userpositions, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "positions_id", referencedColumnName: "positionsId" }])
  positions: Positions;

  @ManyToOne(() => Users, (users) => users.userpositions, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;
}
