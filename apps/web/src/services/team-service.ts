import Query from "./query";

export default class TeamService extends Query {
  constructor() {
    super();
    this.endpoint = '/teams';
  }

  async getDefaultTeams() {
    this.endpoint = '/teams/defaults';
    return await this.all();
  }

  async getTeamById(id: number) {
    this.endpoint = '/teams';
    return await this.show(id.toString());
  }

  async updateTeam(id: number, power: number) {
    this.endpoint = '/teams';
    return await this.update(id.toString(), { power });
  }
}