FROM fmasa/lebeda:7.3-ci

RUN mkdir -p ~/.ssh
RUN ssh-keyscan -H www.skauting.cz >> ~/.ssh/known_hosts
RUN echo "$DEPLOY_SSH_KEY" > ~/.ssh/id_ed25519
RUN ssh-add
RUN phing deploy
