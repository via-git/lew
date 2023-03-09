FROM jupyter/base-notebook:latest

# Install .NET CLI dependencies

ARG NB_USER=jovyan
ARG NB_UID=1000
ENV USER ${NB_USER}
ENV NB_UID ${NB_UID}
ENV HOME /home/${NB_USER}

WORKDIR ${HOME}

USER root
RUN apt-get update
RUN apt-get install -y curl wget git
RUN pip install pytest-playwright && playwright install
RUN mkdir Notebooks

RUN chown -R ${NB_UID} ${HOME}
USER ${USER}

# Set root to Notebooks
WORKDIR ${HOME}/Notebooks/
