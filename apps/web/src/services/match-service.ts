import Query from "./query";

export default class MatchService extends Query {
  constructor() {
    super();
    this.endpoint = '/matches';
  }

  async getMatchById(id: number) {
    this.endpoint = '/matches';
    return await this.show(id.toString());
  }

  async updateMatchResult(id: number, homeScore: number, awayScore: number) {
    this.endpoint = '/matches';
    return await this.update(id.toString(), {
      home_score: homeScore,
      away_score: awayScore,
    });
  }
}