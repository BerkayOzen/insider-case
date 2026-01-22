import Query from './query';

export type TeamInput = {
  name: string;
  power: number;
};

export type InitLeaguePayload = {
  teams: TeamInput[];
};

export default class LeagueService extends Query {
  constructor() {
    super();
    this.endpoint = '/league';
  }

  async init(payload: InitLeaguePayload) {
    this.endpoint = '/league/init';
    return await this.store(payload);
  }

  async state() {
    this.endpoint = '/league/state';
    return await this.all();
  }

  async playWeek() {
    this.endpoint = '/league/play-week';
    return await this.store(null);
  }

  async playAll() {
    this.endpoint = '/league/play-all';
    return await this.store(null);
  }

  async fixtures() {
    this.endpoint = '/league/fixtures';
    return await this.all();
  }

  async reset() {
    this.endpoint = '/league';
    return await this.remove();
  }

}
