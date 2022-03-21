FROM php:7.4-fpm

RUN apt-get update && apt-get install -y cmake gperf libssl-dev zlib1g-dev libreadline-dev git

RUN git clone https://github.com/CopernicaMarketingSoftware/PHP-CPP.git
COPY cpp.patch cpp.patch
RUN cd PHP-CPP && git apply ../cpp.patch && make && make install

RUN git clone --recurse-submodules https://github.com/yaroslavche/phptdlib.git
RUN cd phptdlib && mkdir build && cd build && cmake .. && make && make install