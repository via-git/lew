FROM jupyter/base-notebook:latest

# Install .NET CLI d

ARG NB_USER=jovyan
ARG NB_UID=1000
ENV USER ${NB_USER}
ENV NB_UID ${NB_UID}
ENV HOME /home/${NB_USER}

WORKDIR ${HOME}

USER root
RUN apt-get update
RUN apt-get install -y curl wget git ssh
RUN apt-get install -y dotnet-sdk-6.0
#RUN pip install pytest-playwright && playwright install
#RUN apt-get install -y libglib2.0-0 libnss3 libnspr4 libatk1.0-0 libatk-bridge2.0-0 libcups2 libdrm2 libdbus-1-3 libatspi2.0-0 libpango-1.0-0 libasound2 libxcomposite1 libxdamage1 libxfixes3 libxrandr2 libgbm1 libxkbcommon0 libcairo2

COPY *.py ${HOME}/Notebooks/

RUN chown -R ${NB_UID} ${HOME}
USER ${USER}

# Set root to Notebooks
WORKDIR ${HOME}/Notebooks/
